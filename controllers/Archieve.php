<?php
error_reporting(0);
ini_set('display_errors', 0);
defined('BASEPATH') or exit('No direct script access allowed');

class Archieve extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'workspace_model', 'archieve_model', 'activity_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation', 'pagination']);
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

			$filter = (isset($_GET['filter']) && !empty($_GET['filter'])) ? $_GET['filter'] : '';

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
			$data['is_admin'] =  $this->ion_auth->is_admin();
			$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;
			$data['all_user'] = $this->users_model->get_user($user_ids);
			$admin_ids = explode(',', $current_workspace_id[0]->admin_id);
			$section = array_map('trim', $admin_ids);
			$data['admin_ids'] = $admin_ids = $section;

			$data['archieves'] = $archieves = $this->archieve_model->get_archieve($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter);
			$i = 0;
			foreach ($archieves as $row) {
				$archieves_user_ids = explode(',', $row['user_id']);
				$data['archieves'][$i] = $row;
				$data['archieves'][$i]['archieves_users'] = $this->users_model->get_user_array_responce($archieves_user_ids);
				$i++;
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('archieve', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function get_archieves_list($id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = !empty($id && is_numeric($id)) ? $id : (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : $this->session->userdata('user_id'));
			return $this->archieve_model->get_archieves_list($workspace_id, $user_id);
		}
	}

	public function create()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('archieve_type', str_replace(':', '', 'archieve Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('date', str_replace(':', '', 'date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$admin_id = $this->session->userdata('user_id');
			$workspace_id = $this->session->userdata('workspace_id');
			$admin_ids = $this->users_model->get_workspcae_admin_ids($workspace_id);
			if (!empty($this->input->post('users'))) {
				$user_ids = implode(",", $this->input->post('users')) . ',' . $admin_ids;
			} else {
				if (!is_client()) {
					$user_ids = $this->session->userdata('user_id') . ',' . $admin_ids;
				} else {
					$user_ids =  $admin_ids;
				}
			}
			if (!empty($this->input->post('clients'))) {
				$client_ids = implode(",", $this->input->post('clients'));
			} else {
				if (is_client()) {
					$client_ids = $this->session->userdata('user_id');
				} else {
					$client_ids =  '';
				}
			}

			$data = array(
				'title' => $this->input->post('title'),
				'archieve_type' => strip_tags($this->input->post('archieve_type', true)),
				'description' => $this->input->post('description', true),
				'user_id' => $user_ids,
				'client_id' => $client_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'date' => strip_tags($this->input->post('date', true)),
			);
			$data = escape_array($data);
			$archieve_id = $this->archieve_model->create_archieve($data);

			if ($archieve_id != false) {

				$this->session->set_flashdata('message', 'archieve Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'archieve could not Created! Try again!');
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
		$this->form_validation->set_rules('archieve_type', str_replace(':', '', 'archieve Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('start_date', str_replace(':', '', 'start_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$admin_id = $this->session->userdata('user_id');

			$class_sts = strip_tags($this->input->post('status', true));
			if ($class_sts == 'onhold' || $class_sts == 'cancelled') {
				$class = 'danger';
			} elseif ($class_sts == 'finished') {
				$class = 'success';
			} else {
				$class = 'info';
			}
			// checking for new users

			$user_ids = !empty($this->input->post('users')) ? $this->input->post('users') : array();
			$client_ids = !empty($this->input->post('clients')) ? $this->input->post('clients') : array();
			if (($key = array_search($admin_id, $user_ids)) !== false) {
				unset($user_ids[$key]);
			}
			$user_ids = array_merge($user_ids, $client_ids);

			$archieve_users = $this->archieve_model->get_archieve_users($this->input->post('update_id'));
			$archieve_users = explode(",", $archieve_users[0]['user_id']);
			if (($key = array_search($admin_id, $archieve_users)) !== false) {
				unset($archieve_users[$key]);
			}
			$archieve_clients = $this->archieve_model->get_archieve_clients($this->input->post('update_id'));


			$archieve_clients = explode(",", $archieve_clients[0]['client_id']);
			$archieve_users = array_merge($archieve_clients, $archieve_users);
			if (!empty($user_ids)) {
				$new_users = array();
				for ($i = 0; $i < count($user_ids); $i++) {
					if (!in_array($user_ids[$i], $archieve_users)) {
						array_push($new_users, $user_ids[$i]);
					}
				}
			}
			$workspace_id = $this->session->userdata('workspace_id');
			$admin_ids = $this->users_model->get_workspcae_admin_ids($workspace_id);
			if (!empty($this->input->post('users'))) {
				$user_ids = implode(",", $this->input->post('users')) . ',' . $admin_ids;
			} else {
				$user_ids = $this->session->userdata('user_id') . ',' . $admin_ids;
			}
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'archieve_type' => strip_tags($this->input->post('archieve_type', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'client_id' => (!empty($this->input->post('clients'))) ? implode(",", $this->input->post('clients')) : '',
				'workspace_id' => $this->session->userdata('workspace_id'),
				'date' => strip_tags($this->input->post('start_date', true))
			);
			// $data = escape_array($data);
			if ($this->archieve_model->edit_archieve($data, $this->input->post('update_id'))) {

				$this->session->set_flashdata('message', 'archieve Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'archieve could not Updated! Try again!');
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

	public function get_archieve_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$archieve_id = $this->input->post('id');

			if (empty($archieve_id) || !is_numeric($archieve_id) || $archieve_id < 1) {
				redirect('archieve', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->archieve_model->get_archieve_by_id($archieve_id);

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

		$archieve_id = $this->uri->segment(3);
		if (!empty($archieve_id) && is_numeric($archieve_id)  || $archieve_id < 1) {

			if ($this->archieve_model->delete_archieve($archieve_id)) {

				$this->session->set_flashdata('message', 'archieve deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'archieve could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('archieve', 'refresh');
	}

	public function delete_archieve_file()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$file_id = $this->uri->segment(3);
		if (!empty($file_id) && is_numeric($file_id)  || $file_id < 1) {

			if ($this->archieve_model->delete_file($file_id)) {
				$this->session->set_flashdata('message', 'File deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'File could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
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
			}
			$data['is_admin'] =  $this->ion_auth->is_admin();
			$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;
			$data['all_user'] = $this->users_model->get_user($user_ids);
			$archieve_id = $this->uri->segment(3);

			if (empty($archieve_id) || !is_numeric($archieve_id) || $archieve_id < 1) {
				redirect('archieve', 'refresh');
				return false;
				exit(0);
			}
			$user_id = $this->session->userdata('user_id');
			$notification_id = $this->notifications_model->get_id_by_type_id($archieve_id, 'archieve', $user_id);
			if (!empty($notification_id) && isset($notification_id[0])) {
				$notification_id = $notification_id[0]['id'];
				$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
			}
			$archieves = $this->archieve_model->get_archieve_by_id($archieve_id);
			if (!empty($archieves) && isset($archieves[0])) {

				$archieves_user_ids = explode(',', $archieves[0]['user_id']);
				$archieves_client_ids = explode(',', $archieves[0]['client_id']);
				$archieve_users = array_merge($archieves_user_ids, $archieves_client_ids);

				if (in_array($user_id, $archieve_users) || is_admin()) {
					$data['archieves'] = $archieves[0];


					$data['archieves']['archieves_users'] = $this->users_model->get_user_array_responce($archieves_user_ids);


					$data['archieves']['archieves_clients'] = $this->users_model->get_user_array_responce($archieves_client_ids);

					$type = 'archieve';
					$data['files'] = $this->archieve_model->get_files($archieve_id, $type);

					$workspace_id = $this->session->userdata('workspace_id');
					if (!empty($workspace_id)) {
						$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
						$this->load->view('archieve-details', $data);
					} else {
						redirect('home', 'refresh');
					}
				} else {
					$this->session->set_flashdata('message', 'You are not authorized to view this archieve.');
					$this->session->set_flashdata('message_type', 'error');
					redirect('archieve', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'This archieve was deleted.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
			}
		}
	}

	public function add_archieve_file()
	{
		$id = '';
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('workspace_id', str_replace(':', '', 'workspace_id is empty.'), 'trim|required');
		$this->form_validation->set_rules('archieve_id', str_replace(':', '', 'archieve_id is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			if (!empty($_FILES['file']['name'])) {
				if (!is_dir('./assets/archieve/')) {
					mkdir('./assets/archieve/', 0777, TRUE);
				}
				$config['upload_path']          = './assets/archieve/';
				$config['allowed_types']        = $this->config->item('allowed_types');
				$config['overwrite']            = false;
				$config['max_size']             = 10000;
				$config['max_width']            = 0;
				$config['max_height']           = 0;

				$this->load->library('upload', $config);

				if ($this->upload->do_upload('file')) {
					$file_data = $this->upload->data();
					$data = array(
						'original_file_name' => $file_data['orig_name'],
						'file_name' => $file_data['file_name'],
						'file_extension' => $file_data['file_ext'],
						'file_size' => $this->custom_funcation_model->format_size_units($file_data['file_size']),
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->input->post('workspace_id'),
						'type' => 'archieve',
						'type_id' => $this->input->post('archieve_id')
					);
					$id = $this->archieve_model->add_file($data);
					if ($id != false) {
						$this->session->set_flashdata('message', 'File(s) uploaded successfully.');
						$this->session->set_flashdata('message_type', 'success');
					} else {
						$this->session->set_flashdata('message', 'Something went wrong please try again.');
						$this->session->set_flashdata('message_type', 'error');
					}
				} else {
					$response['error'] = true;
					$response['csrfName'] = $this->security->get_csrf_token_name();
					$response['csrfHash'] = $this->security->get_csrf_hash();
					echo json_encode($response);
					$this->session->set_flashdata('message', 'Sorry! this type of file not allowed');
					$this->session->set_flashdata('message_type', 'error');
				}
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

	public function get_archieve_files()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$this->form_validation->set_rules('archieve_id', str_replace(':', '', 'archieve_id is empty.'), 'trim|required');

			if ($this->form_validation->run() === TRUE) {
				$type = 'archieve';
				$archieve_id = $this->input->post('archieve_id');
				$data = $this->archieve_model->get_files($archieve_id, $type);

				$response['error'] = false;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['data'] = $data;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = false;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = validation_errors();
				echo json_encode($response);
			}
		}
	}
}
