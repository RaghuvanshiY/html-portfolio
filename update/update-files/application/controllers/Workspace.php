<?php defined('BASEPATH') or exit('No direct script access allowed');

class Workspace extends CI_Controller
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'users_model', 'notifications_model']);
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
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$response['error'] = true;
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'This operation not allowed in demo version';
			echo json_encode($response);
			return false;
			exit();
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'user_id' => $this->session->userdata('user_id'),
				'admin_id' => $this->session->userdata('user_id'),
				'created_by' => $this->session->userdata('user_id')
			);
			$workspace_id = $this->workspace_model->create_workspace($data);

			if (!empty($workspace_id)) {
				$this->session->set_flashdata('message', 'Workspace Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Workspace could not Created! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}

			$this->workspace_model->add_workspace_ids_to_users($workspace_id, $this->session->userdata('user_id'));

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
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$response['error'] = true;
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'This operation not allowed in demo version';
			echo json_encode($response);
			return false;
			exit();
		}
		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'required');

		if ($this->form_validation->run() === TRUE) {
			if (!$this->session->userdata('workspace_id')) {

				$this->session->set_flashdata('message', 'Workspace could not updated! Try again!');
				$this->session->set_flashdata('message_type', 'error');
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'Successful';
				echo json_encode($response);
				return false;
			}

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'status' => strip_tags($this->input->post('status', true))
			);
			$current_workspace_id = $this->session->userdata('workspace_id');
			$workspace_id = $this->input->post('workspace_id');
			$status = $this->input->post('status');
			// $workspace_count = $this->workspace_model->get_workspace_count(1);
			if ($workspace_id == $current_workspace_id && $status == 0) {
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = "You can't deactivate current workspace please switch workspace";
				echo json_encode($response);
				return false;
			}
			if ($this->workspace_model->edit_workspace($workspace_id, $data)) {
				$this->session->set_flashdata('message', 'Workspace updated successfully.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', 'Workspace could not updated! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = validation_errors();
				echo json_encode($response);
			}
		} else {
			$response['error'] = true;

			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}

	public function change()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$workspace_id = $this->uri->segment(3);

		if (!empty($workspace_id) && is_numeric($workspace_id) || $workspace_id < 1) {
			$workspace = $this->workspace_model->get_workspace_by_id($workspace_id);
			if (!empty($workspace) && isset($workspace[0])) {
				if ($workspace[0]['status'] == 1) {
					if ($this->session->set_userdata('workspace_id', $workspace_id)) {
						$this->session->set_flashdata('message', 'Something Wrong. Workspace Not Changed.');
						$this->session->set_flashdata('message_type', 'error');
					} else {
						$this->session->set_flashdata('message', 'Workspace Changed successfully.');
						$this->session->set_flashdata('message_type', 'success');
					}
				} else {
					$this->session->set_flashdata('message', 'This workspace seems deactive.');
					$this->session->set_flashdata('message_type', 'error');
				}
			} else {
				$this->session->set_flashdata('message', 'Something went wrong please try again.');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('home', 'refresh');
	}

	public function manage_workspaces()
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
				$data['notifications'] = $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace[0]->id);
				$this->load->view('manage-workspaces', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}
	public function get_workspace_list()
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
			return $this->workspace_model->get_workspace_list();
		}
	}
	public function get_workspace_by_id()
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

			$id = $this->uri->segment(3);

			if (empty($id) || !is_numeric($id)) {
				redirect('home', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->workspace_model->get_workspace_by_id($id);
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
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
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version!');
			$this->session->set_flashdata('message_type', 'error');
			$response['error'] = true;
			echo json_encode($response);
			return false;
			exit();
		}
		$id = $this->uri->segment(3);
		$current_workspace_id = $this->session->userdata('workspace_id');
		if (!empty($id) && is_numeric($id)  || $id < 1) {
			$workspace_count = $this->workspace_model->get_workspace_count();
			if ($id !=  $current_workspace_id) {
				if ($this->workspace_model->delete_workspace($id)) {
					$this->session->set_flashdata('message', 'Workspace deleted successfully.');
					$this->session->set_flashdata('message_type', 'success');
					$response['error'] = false;
				} else {
					$this->session->set_flashdata('message', 'Workspace could not be deleted! Try again!');
					$this->session->set_flashdata('message_type', 'error');
					$response['error'] = true;
				}
			} else {
				$this->session->set_flashdata('message', "You can't delete current workspace please switch workspace");
				$this->session->set_flashdata('message_type', 'error');
				$response['error'] = true;
			}
		}
		echo json_encode($response);
		// redirect('home', 'refresh');
	}
}
