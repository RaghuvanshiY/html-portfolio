<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'workspace_model', 'notifications_model']);
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

			if (is_client()) {
				redirect('home', 'refresh');
			}

			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			$workspace_ids = explode(',', $user->workspace_id);

			$section = array_map('trim', $workspace_ids);

			$workspace_ids = $section;

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($workspace_ids);
			if (!empty($workspace)) {
				if (!$this->session->has_userdata('workspace_id')) {
					$this->session->set_userdata('workspace_id', $workspace[0]->id);
				}
			}
			$data['is_admin'] =  $this->ion_auth->is_admin();

			$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;

			$data['all_user'] = $this->users_model->get_user($user_ids);
			$data['not_in_workspace_user'] = $this->users_model->get_user_not_in_workspace($user_ids);

			$admin_ids = explode(',', $current_workspace_id[0]->admin_id);
			$section = array_map('trim', $admin_ids);
			$data['admin_ids'] = $admin_ids = $section;

			$super_admin_ids = $this->users_model->get_all_super_admins_id(1);

			foreach ($super_admin_ids as $super_admin_id) {
				$temp_ids[] = $super_admin_id['user_id'];
			}
			$data['super_admin_ids'] = $temp_ids;
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('clients', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}
	function deactive($id = '')
	{
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$id = !empty($id) ? $id : $this->uri->segment(3);

		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin() && !empty($id) && is_numeric($id)) {
			$activation = $this->ion_auth->deactivate($id);

			if ($activation) {
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = $this->ion_auth->messages();
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = $this->ion_auth->errors();
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			}
		} else {

			$response['error'] = true;
			$response['message'] = $this->ion_auth->errors();
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($response);
		}
	}

	function activate($id = '')
	{
		$id = !empty($id) ? $id : $this->uri->segment(3);

		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin() && !empty($id) && is_numeric($id)) {
			$activation = $this->ion_auth->activate($id);

			if ($activation) {
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = $this->ion_auth->messages();
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = $this->ion_auth->errors();
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			}
		} else {

			$response['error'] = true;
			$response['message'] = $this->ion_auth->errors();
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($response);
		}
	}


	public function get_users_list()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			if (is_client()) {
				redirect('home', 'refresh');
			}

			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = $this->session->userdata('user_id');
			return $this->users_model->get_users_list($workspace_id, $user_id);
		}
	}

	function search_user_by_email($email = '')
	{
		if ($this->ion_auth->logged_in() && !empty(trim($email))) {
			$data = $this->users_model->get_users_by_email($email);
			if (!empty($data) && isset($data[0]['password'])) {
				unset($data[0]['password']);

				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();

				print_r(json_encode($data));
			} else {

				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();

				print_r(json_encode($data));
			}
		} else {
			return false;
		}
	}

	function get_user_by_id($id = '')
	{

		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			$data = $this->users_model->get_user_by_id($id);
			if (!empty($data) && isset($data[0]['password'])) {
				unset($data[0]['password']);

				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();

				print_r(json_encode($data));
			} else {

				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();

				print_r(json_encode($data));
			}
		} else {


			$data[0]['email'] = '';
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			print_r(json_encode($data));
		}
	}

	function make_user_admin($id = '')
	{
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$id = !empty($id) ? $id : $this->uri->segment(3);
		$workspace_id = $this->session->userdata('workspace_id');
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			if ($this->users_model->make_user_admin($workspace_id, $id)) {
				$this->session->set_flashdata('message', 'Member Added as a Admin.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', 'Admin Added as a Member.');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			}
		} else {

			$response['error'] = true;
			$response['message'] = 'Successful';
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($response);
		}
	}

	function make_user_super_admin($id = '')
	{
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$id = !empty($id) ? $id : $this->uri->segment(3);
		$workspace_id = $this->session->userdata('workspace_id');
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			if ($this->users_model->make_user_admin($workspace_id, $id)) {

				$this->users_model->make_user_super_admin($id);

				$this->session->set_flashdata('message', 'Member Added as a Super Admin.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', 'Super Admin Added as a Member.');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			}
		} else {

			$response['error'] = true;
			$response['message'] = 'Successful';
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($response);
		}
	}
	function remove_user_from_admin($id = '')
	{
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$id = !empty($id) ? $id : $this->uri->segment(3);
		$workspace_id = $this->session->userdata('workspace_id');
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			$is_admin = ($this->ion_auth->is_admin()) ? true : '';
			if ($this->users_model->remove_user_from_admin($workspace_id, $id, $is_admin)) {
				$this->session->set_flashdata('message', 'Member removed from Admin.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', 'Member Added as an Admin.');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Successful';
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($response);
			}
		} else {

			$response['error'] = true;
			$response['message'] = 'Successful';
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($response);
		}
	}

	function remove_user_from_workspace($id = '')
	{
		if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
			$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}
		$id = !empty($id) ? $id : $this->uri->segment(3);
		$workspace_id = $this->session->userdata('workspace_id');
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			if ($this->users_model->remove_user_from_workspace($workspace_id, $id, 'remove')) {
				$this->session->set_flashdata('message', 'Member removed from Workspace.');
				$this->session->set_flashdata('message_type', 'success');
				if ($id == $this->session->userdata('user_id')) {
					$this->session->unset_userdata('workspace_id');
					redirect('home', 'refresh');
				} else {
					redirect('users', 'refresh');
				}
				return true;
			} else {
				$this->session->set_flashdata('message', 'Not Successful.');
				$this->session->set_flashdata('message_type', 'error');
				return false;
			}
		} else {
			return false;
		}
	}

	public function edit_profile()
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
			if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
				$this->session->set_flashdata('message', 'This operation not allowed in demo version.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
			$user_id = $this->uri->segment(3);
			if (!empty($user_id) && is_numeric($user_id)  || $user_id < 1) {
				$data['user'] = $user = $this->users_model->get_user_by_id($user_id, true);
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
					$data['is_admin'] =  $this->ion_auth->is_admin();
					$this->load->view('edit-profile', $data);
				} else {
					redirect('home', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'Invalid access detected!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
		}
	}
}
