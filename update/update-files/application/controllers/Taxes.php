<?php
/* <!--  START 
===============================================
Version :- V.2 		Author : ''    23-July-2020
=============================================== 
-->
*/
defined('BASEPATH') or exit('No direct script access allowed');

class Taxes extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'tax_model', 'notifications_model']);
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
		} else {
			if (!is_admin()) {
				$this->session->set_flashdata('message', 'You are not authorized to access this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			$product_ids = explode(',', $user->workspace_id);

			$section = array_map('trim', $product_ids);

			$product_ids = $section;

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($product_ids);
			if (!empty($workspace)) {
				if (!$this->session->has_userdata('workspace_id')) {
					$this->session->set_userdata('workspace_id', $workspace[0]->id);
				}
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('taxes', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function create()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		$this->form_validation->set_rules('percentage', str_replace(':', '', 'percentage is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'percentage' => strip_tags($this->input->post('percentage', true)),
				'workspace_id' => $this->session->userdata('workspace_id')
			);
			$tax_id = $this->tax_model->add_tax($data);

			if ($tax_id != false) {
				$this->session->set_flashdata('message', 'Tax Added successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Tax could not Created! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}

			$response['error'] = false;
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'Successful';
			echo json_encode($response);
		} else {
			$response['error'] = true;
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}


	public function get_tax_by_id($id = '')
	{
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			if (!is_admin()) {
				$this->session->set_flashdata('message', 'You are not authorized to access this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
			$data = $this->tax_model->get_tax_by_id($id);
			if (!empty($data)) {
				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($data[0]);
			} else {
				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($data[0]);
			}
		} else {
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
		}
	}

	public function edit()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		$this->form_validation->set_rules('percentage', str_replace(':', '', 'percentage is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_id', str_replace(':', '', 'update_id is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'percentage' => strip_tags($this->input->post('percentage', true)),
				'description' => strip_tags($this->input->post('description', true))
			);

			if ($this->tax_model->edit_tax($data, $this->input->post('update_id'))) {
				$this->session->set_flashdata('message', 'Tax Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Tax could not Updated! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}

			$response['error'] = false;

			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'Successful';
			echo json_encode($response);
		} else {
			$response['error'] = true;
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}

	public function get_tax_list()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			if (!is_admin()) {
				$this->session->set_flashdata('message', 'You are not authorized to access this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
			$workspace_id = $this->session->userdata('workspace_id');
			return $this->tax_model->get_tax_list($workspace_id);
		}
	}

	public function delete()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$tax_id = $this->uri->segment(3);
		if (!empty($tax_id) && is_numeric($tax_id)  || $tax_id < 1) {
			if ($this->tax_model->delete_tax($tax_id)) {
				$this->session->set_flashdata('message', 'Tax deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Tax could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('tax', 'refresh');
	}
}
/* 
// END
// ===============================================
// Version : V.2 		Author : ''    23-July-2020
// =============================================== 
*/
