<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Activity_logs extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'activity_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
	}

	public function index()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else
		if (is_admin()) {
			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			$product_ids = explode(',', $user->workspace_id);

			$section = array_map('trim', $product_ids);

			$product_ids = $section;

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($product_ids);
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$data['is_admin'] =  is_admin();
				$this->load->view('activity-logs', $data);
			} else {
				redirect('home', 'refresh');
			}
		} else {
			$this->session->set_flashdata('message', 'You are not authorized to view this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
		}
	}

	public function delete()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		if (is_admin()) {
			$activity_id = $this->uri->segment(3);

			if (!empty($activity_id) && is_numeric($activity_id)) {
				if ($this->activity_model->delete_activity($activity_id)) {
					$this->session->set_flashdata('message', 'Activity deleted successfully.');
					$this->session->set_flashdata('message_type', 'success');
					$response['error'] = false;
					$response['message'] = 'Activity deleted successfully';
					echo json_encode($response);
				} else {
					$this->session->set_flashdata('message', 'Activity could not be deleted! Try again!');
					$this->session->set_flashdata('message_type', 'error');
					$response['error'] = true;
					$response['message'] = 'Activity could not be deleted! Try again!';
					echo json_encode($response);
				}
			}
			redirect('activity_logs', 'refresh');
		} else {
			$response['error'] = true;

			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'You are not authorized to delete activity log';
			echo json_encode($response);
		}
	}

	public function get_activity_logs_list()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			return $this->activity_model->get_activity_list($this->session->userdata('user_id'), $workspace_id);
		}
	}
}
