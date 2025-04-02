<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Milestones extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'workspace_model', 'projects_model']);
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
				$data['is_admin'] =  $this->ion_auth->is_admin();
				$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
				$user_ids = explode(',', $current_workspace_id[0]->user_id);
				$section = array_map('trim', $user_ids);
				$user_ids = $section;
				$data['all_user'] = $this->users_model->get_user($user_ids);
				$admin_ids = explode(',', $current_workspace_id[0]->admin_id);
				$section = array_map('trim', $admin_ids);
				$data['admin_ids'] = $admin_ids = $section;
				$data['projects'] = $projects = $this->projects_model->get_project($this->session->userdata('workspace_id'), $this->session->userdata('user_id'));
				$i = $j = 0;
				$data['notifications'] = !empty($current_workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $current_workspace_id) : array();
				$data['is_admin'] =  $this->ion_auth->is_admin();
				$this->load->view('projects', $data);
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
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('budget', str_replace(':', '', 'budget is empty.'), 'trim|required');
		$this->form_validation->set_rules('start_date', str_replace(':', '', 'start_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('end_date', str_replace(':', '', 'end_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$user_ids = implode(",", $this->input->post('users'));
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'status' => strip_tags($this->input->post('status', true)),
				'budget' => strip_tags($this->input->post('budget', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);
			$project_id = $this->projects_model->create_project($data);

			if ($project_id != false) {
				$this->session->set_flashdata('message', 'Project Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Project could not Created! Try again!');
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
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('budget', str_replace(':', '', 'budget is empty.'), 'trim|required');
		$this->form_validation->set_rules('start_date', str_replace(':', '', 'start_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('end_date', str_replace(':', '', 'end_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$user_ids = implode(",", $this->input->post('users'));
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'status' => strip_tags($this->input->post('status', true)),
				'budget' => strip_tags($this->input->post('budget', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);

			if ($this->projects_model->edit_project($data, $this->input->post('update_id'))) {
				$this->session->set_flashdata('message', 'Project Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Project could not Updated! Try again!');
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

	public function get_project_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$project_id = $this->input->post('id');

			if (empty($project_id) || !is_numeric($project_id) || $project_id < 1) {
				redirect('projects', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->projects_model->get_project_by_id($project_id);
			echo json_encode($data[0]);
		}
	}

	public function delete()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$project_id = $this->uri->segment(3);
		if (!empty($project_id) && is_numeric($project_id) || $project_id < 1) {
			if ($this->projects_model->delete_project($project_id)) {
				$this->session->set_flashdata('message', 'Project deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Project could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('projects', 'refresh');
	}



	public function details()
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
				$data['is_admin'] =  $this->ion_auth->is_admin();
				$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
				$user_ids = explode(',', $current_workspace_id[0]->user_id);
				$section = array_map('trim', $user_ids);
				$user_ids = $section;
				$data['all_user'] = $this->users_model->get_user($user_ids);
				$project_id = $this->uri->segment(3);

				if (empty($project_id) || !is_numeric($project_id) || $project_id < 1) {
					redirect('projects', 'refresh');
					return false;
					exit(0);
				}

				$projects = $this->projects_model->get_project_by_id($project_id);
				$data['projects'] = $projects[0];
				$this->load->view('project-details', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function tasks()
	{
		$this->load->view('tasks');
	}
}
