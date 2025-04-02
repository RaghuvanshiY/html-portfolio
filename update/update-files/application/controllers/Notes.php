<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notes extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['notes_model', 'workspace_model', 'notifications_model']);
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
			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			$product_ids = explode(',', $user->workspace_id);

			$section = array_map('trim', $product_ids);

			$product_ids = $section;

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($product_ids);
			if (!empty($workspace)) {
				if (!$this->session->has_userdata('workspace_id')) {
					$this->session->set_userdata('workspace_id', $workspace[0]->id);
				}
				if ($this->session->has_userdata('workspace_id')) {
					$notes = $this->notes_model->get_note($this->session->userdata('workspace_id'), $this->session->userdata('user_id'));

					if (!empty($notes)) {
						$data['notes'] = $notes;
					}
				}
				$workspace_id = $this->session->userdata('workspace_id');
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$data['is_admin'] =  $this->ion_auth->is_admin();
				$this->load->view('notes', $data);
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

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'class' => strip_tags($this->input->post('class', true)),
				'user_id' => $this->session->userdata('user_id'),
				'description' => strip_tags($this->input->post('description', true)),
				'workspace_id' => $this->session->userdata('workspace_id')
			);
			$note_id = $this->notes_model->create_note($data);

			if ($note_id != false) {
				$this->session->set_flashdata('message', 'Note Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Note could not Created! Try again!');
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

	public function edit()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_id', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'class' => strip_tags($this->input->post('class', true)),
				'description' => strip_tags($this->input->post('description', true))
			);

			if ($this->notes_model->edit_note($data, $this->input->post('update_id'))) {
				$this->session->set_flashdata('message', 'Note Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Note could not Updated! Try again!');
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

	public function delete()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$note_id = $this->uri->segment(3);
		$user_id = $_SESSION['user_id'];

		if (!empty($note_id) && is_numeric($note_id)  || $note_id < 1) {
			if ($this->notes_model->delete_note($note_id, $user_id)) {
				$this->session->set_flashdata('message', 'Note deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Note could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('notes', 'refresh');
	}

	public function get_note_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$note_id = $this->input->post('id');

			if (empty($note_id) || !is_numeric($note_id) || $note_id < 1) {
				redirect('notes', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->notes_model->get_note_by_id($note_id);

			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}
}
