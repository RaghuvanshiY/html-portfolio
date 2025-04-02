<?php
error_reporting(0);
ini_set('display_errors', 0);
defined('BASEPATH') or exit('No direct script access allowed');

class Reporting extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'workspace_model', 'reporting_model', 'activity_model', 'notifications_model']);
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

			$data['reports'] = $reports = $this->reporting_model->get_report($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter);
			$i = 0;
			foreach ($reports as $row) {
				$reports_user_ids = explode(',', $row['user_id']);
				$data['reports'][$i] = $row;
				$data['reports'][$i]['reports_users'] = $this->users_model->get_user_array_responce($reports_user_ids);
				$i++;
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('reporting', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function get_reports_list($id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = !empty($id && is_numeric($id)) ? $id : (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : $this->session->userdata('user_id'));
			return $this->reporting_model->get_reports_list($workspace_id, $user_id);
		}
	}

	public function create()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('report_type', str_replace(':', '', 'Report Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('start_date', str_replace(':', '', 'start_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('end_date', str_replace(':', '', 'end_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$start_date = strip_tags($this->input->post('start_date', true));
			$end_date = strip_tags($this->input->post('end_date', true));

			if ($end_date < $start_date) {
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'End date should not be lesser then start date.';
				echo json_encode($response);
				return false;
			}

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

			$class_sts = strip_tags($this->input->post('status', true));
			if ($class_sts == 'onhold' || $class_sts == 'cancelled') {
				$class = 'danger';
			} elseif ($class_sts == 'finished') {
				$class = 'success';
			} else {
				$class = 'info';
			}
			$data = array(
				'title' => $this->input->post('title'),
				'report_type' => strip_tags($this->input->post('report_type', true)),
				'status' => strip_tags($this->input->post('status', true)),
				'class' => $class,
				'description' => $this->input->post('description', true),
				'user_id' => $user_ids,
				'client_id' => $client_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);
			$data = escape_array($data);
			$report_id = $this->reporting_model->create_report($data);

			if ($report_id != false) {
				// preparing activity log data
				$activity_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'user_name' => get_user_name(),
					'type' => 'Report',
					'report_id' => $report_id,
					'report_title' => strip_tags($this->input->post('title', true)),
					'activity' => 'Created',
					'message' => get_user_name() . ' Created Report ' . strip_tags($this->input->post('title', true)),
				);
				$this->activity_model->store_activity($activity_data);

				//preparing notification data
				if (!empty($this->input->post('users')) || !empty($this->input->post('clients'))) {

					$user_ids = !empty($this->input->post('users')) ? $this->input->post('users') : array();
					$client_ids = !empty($this->input->post('clients')) ? $this->input->post('clients') : array();
					$user_ids = array_merge($user_ids, $client_ids);
					if (($key = array_search($admin_id, $user_ids)) !== false) {
						unset($user_ids[$key]);
					}
					$user_ids = implode(",", $user_ids);
					$report = $this->reporting_model->get_report_by_id($report_id);
					$admin = $this->users_model->get_user_by_id($admin_id);
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$notification = $admin_name . " Sent you new report - <b>" . $report[0]['title'] . "</b> ID <b>#" . $report[0]['id'] . "</b>.";
					$title = $admin_name . " sent you new report <b>" . $report[0]['title'] . "</b>.";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'report',
						'type_id' => $report_id,
						'notification' => $notification,
					);
					if (!empty($user_ids)) {
						$user_ids = explode(",", $user_ids);
						$this->notifications_model->store_notification($notification_data);
					}
				}
				$this->session->set_flashdata('message', 'Report Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Report could not Created! Try again!');
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
		$this->form_validation->set_rules('report_type', str_replace(':', '', 'Report Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('start_date', str_replace(':', '', 'start_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('end_date', str_replace(':', '', 'end_date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$start_date = strip_tags($this->input->post('start_date', true));
			$end_date = strip_tags($this->input->post('end_date', true));

			if ($end_date < $start_date) {
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'End date should not be lesser then start date.';
				echo json_encode($response);
				return false;
			}

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

			$report_users = $this->reporting_model->get_report_users($this->input->post('update_id'));
			$report_users = explode(",", $report_users[0]['user_id']);
			if (($key = array_search($admin_id, $report_users)) !== false) {
				unset($report_users[$key]);
			}
			$report_clients = $this->reporting_model->get_report_clients($this->input->post('update_id'));


			$report_clients = explode(",", $report_clients[0]['client_id']);
			$report_users = array_merge($report_clients, $report_users);
			if (!empty($user_ids)) {
				$new_users = array();
				for ($i = 0; $i < count($user_ids); $i++) {
					if (!in_array($user_ids[$i], $report_users)) {
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
				'status' => strip_tags($this->input->post('status', true)),
				'class' => $class,
				'report_type' => strip_tags($this->input->post('report_type', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'client_id' => (!empty($this->input->post('clients'))) ? implode(",", $this->input->post('clients')) : '',
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);
			// $data = escape_array($data);
			if ($this->reporting_model->edit_report($data, $this->input->post('update_id'))) {

				$this->session->set_flashdata('message', 'report Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'report could not Updated! Try again!');
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

	public function get_report_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$report_id = $this->input->post('id');

			if (empty($report_id) || !is_numeric($report_id) || $report_id < 1) {
				redirect('reporting', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->reporting_model->get_report_by_id($report_id);

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

		$report_id = $this->uri->segment(3);
		if (!empty($report_id) && is_numeric($report_id)  || $report_id < 1) {

			if ($this->reporting_model->delete_report($report_id)) {

				$this->session->set_flashdata('message', 'report deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'report could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('reporting', 'refresh');
	}

	public function delete_report_file()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$file_id = $this->uri->segment(3);
		if (!empty($file_id) && is_numeric($file_id)  || $file_id < 1) {

			if ($this->reporting_model->delete_file($file_id)) {
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
			$report_id = $this->uri->segment(3);

			if (empty($report_id) || !is_numeric($report_id) || $report_id < 1) {
				redirect('reporting', 'refresh');
				return false;
				exit(0);
			}
			$user_id = $this->session->userdata('user_id');
			$notification_id = $this->notifications_model->get_id_by_type_id($report_id, 'report', $user_id);
			if (!empty($notification_id) && isset($notification_id[0])) {
				$notification_id = $notification_id[0]['id'];
				$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
			}
			$reports = $this->reporting_model->get_report_by_id($report_id);
			if (!empty($reports) && isset($reports[0])) {

				$reports_user_ids = explode(',', $reports[0]['user_id']);
				$reports_client_ids = explode(',', $reports[0]['client_id']);
				$report_users = array_merge($reports_user_ids, $reports_client_ids);

				if (in_array($user_id, $report_users) || is_admin()) {
					$data['reports'] = $reports[0];


					$data['reports']['reports_users'] = $this->users_model->get_user_array_responce($reports_user_ids);


					$data['reports']['reports_clients'] = $this->users_model->get_user_array_responce($reports_client_ids);

					$type = 'report';
					$data['files'] = $this->reporting_model->get_files($report_id, $type);

					$workspace_id = $this->session->userdata('workspace_id');
					if (!empty($workspace_id)) {
						$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
						$this->load->view('report-details', $data);
					} else {
						redirect('home', 'refresh');
					}
				} else {
					$this->session->set_flashdata('message', 'You are not authorized to view this report.');
					$this->session->set_flashdata('message_type', 'error');
					redirect('reporting', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'This report was deleted.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
			}
		}
	}

	public function add_report_file()
	{
		$id = '';
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('workspace_id', str_replace(':', '', 'workspace_id is empty.'), 'trim|required');
		$this->form_validation->set_rules('report_id', str_replace(':', '', 'report_id is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			if (!empty($_FILES['file']['name'])) {
				if (!is_dir('./assets/report/')) {
					mkdir('./assets/report/', 0777, TRUE);
				}
				$config['upload_path']          = './assets/report/';
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
						'type' => 'report',
						'type_id' => $this->input->post('report_id')
					);
					$id = $this->reporting_model->add_file($data);
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

	public function get_report_files()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$this->form_validation->set_rules('report_id', str_replace(':', '', 'report_id is empty.'), 'trim|required');

			if ($this->form_validation->run() === TRUE) {
				$type = 'report';
				$report_id = $this->input->post('report_id');
				$data = $this->reporting_model->get_files($report_id, $type);

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
