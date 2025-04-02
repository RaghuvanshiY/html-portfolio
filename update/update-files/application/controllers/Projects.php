<?php
error_reporting(0);
ini_set('display_errors', 0);
defined('BASEPATH') or exit('No direct script access allowed');

class Projects extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'workspace_model', 'projects_model', 'milestones_model', 'tasks_model', 'activity_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation', 'pagination']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');
		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');
	}

	public function lists()
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

			$this->config->load('taskhub');
			$data['progress_bar_classes'] = $this->config->item('progress_bar_classes');

			$data['projects'] = $projects = $this->projects_model->get_project($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter);
			$i = 0;
			foreach ($projects as $row) {
				$projects_user_ids = explode(',', $row['user_id']);
				$data['projects'][$i] = $row;
				$data['projects'][$i]['project_progress'] = $this->projects_model->get_project_progress($row['id'], $this->session->userdata('workspace_id'));
				$data['projects'][$i]['projects_users'] = $this->users_model->get_user_array_responce($projects_user_ids);
				$i++;
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('projects-list', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function get_projects_list($id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = !empty($id && is_numeric($id)) ? $id : (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : $this->session->userdata('user_id'));
			return $this->projects_model->get_projects_list($workspace_id, $user_id);
		}
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

			$this->config->load('taskhub');
			$data['progress_bar_classes'] = $this->config->item('progress_bar_classes');
			$user_type = is_client() ? 'client' : 'normal';

			$projects_counts = $this->projects_model->get_project($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter, $user_type);

			$config = array();
			$config["base_url"] = base_url('projects');
			$config["total_rows"] = count($projects_counts);
			$config["per_page"] = 10;
			$config["uri_segment"] = 2;

			$config['next_link']        = '›';
			$config['prev_link']        = '‹';
			$config['first_link']       = false;
			$config['last_link']        = false;
			$config['full_tag_open']    = '<ul class="pagination justify-content-center">';
			$config['full_tag_close']   = '</ul>';
			$config['attributes']       = ['class' => 'page-link'];
			$config['first_tag_open']   = '<li class="page-item">';
			$config['first_tag_close']  = '</li>';
			$config['prev_tag_open']    = '<li class="page-item">';
			$config['prev_tag_close']   = '</li>';
			$config['next_tag_open']    = '<li class="page-item">';
			$config['next_tag_close']   = '</li>';
			$config['last_tag_open']    = '<li class="page-item">';
			$config['last_tag_close']   = '</li>';
			$config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">';
			$config['cur_tag_close']    = '<span class="sr-only">(current)</span></span></li>';
			$config['num_tag_open']     = '<li class="page-item">';
			$config['num_tag_close']    = '</li>';

			$this->pagination->initialize($config);

			$page = ($this->uri->segment(2) && is_numeric($this->uri->segment(2))) ? $this->uri->segment(2) : 0;
			$data["links"] = $this->pagination->create_links();

			$data['projects'] = $projects = $this->projects_model->get_project($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter, $user_type, $config["per_page"], $page);

			$i = 0;
			foreach ($projects as $row) {
				$projects_user_ids = explode(',', $row['user_id']);
				$projects_client_ids = explode(',', $row['client_id']);
				$data['projects'][$i] = $row;
				$data['projects'][$i]['project_progress'] = $this->projects_model->get_project_progress($row['id'], $this->session->userdata('workspace_id'));
				$data['projects'][$i]['projects_users'] = $this->users_model->get_user_array_responce($projects_user_ids);
				$data['projects'][$i]['projects_clients'] = $this->users_model->get_user_array_responce($projects_client_ids);
				$i++;
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
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
				'title' => $this->input->post('title', true),
				'status' => strip_tags($this->input->post('status', true)),
				'class' => $class,
				'budget' => $this->input->post('budget') ? strip_tags($this->input->post('budget', true)) : 0,
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'client_id' => $client_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);
			$data = escape_array($data);
			$project_id = $this->projects_model->create_project($data);

			if ($project_id != false) {
				// preparing activity log data
				$activity_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'user_name' => get_user_name(),
					'type' => 'Project',
					'project_id' => $project_id,
					'project_title' => strip_tags($this->input->post('title', true)),
					'activity' => 'Created',
					'message' => get_user_name() . ' Created Project ' . strip_tags($this->input->post('title', true)),
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
					$project = $this->projects_model->get_project_by_id($project_id);
					$admin = $this->users_model->get_user_by_id($admin_id);
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$notification = $admin_name . " assigned you new project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>.";
					$title = $admin_name . " assigned you new project <b>" . $project[0]['title'] . "</b>.";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'project',
						'type_id' => $project_id,
						'notification' => $notification,
					);
					if (!empty($user_ids)) {
						$user_ids = explode(",", $user_ids);
						$this->projects_model->send_email($user_ids, $project_id, $admin_id);
						$this->notifications_model->store_notification($notification_data);
					}
				}
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

			$project_users = $this->projects_model->get_project_users($this->input->post('update_id'));
			$project_users = explode(",", $project_users[0]['user_id']);
			if (($key = array_search($admin_id, $project_users)) !== false) {
				unset($project_users[$key]);
			}
			$project_clients = $this->projects_model->get_project_clients($this->input->post('update_id'));


			$project_clients = explode(",", $project_clients[0]['client_id']);
			$project_users = array_merge($project_clients, $project_users);
			if (!empty($user_ids)) {
				$new_users = array();
				for ($i = 0; $i < count($user_ids); $i++) {
					if (!in_array($user_ids[$i], $project_users)) {
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
				'budget' => $this->input->post('budget') ? strip_tags($this->input->post('budget', true)) : 0,
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'client_id' => (!empty($this->input->post('clients'))) ? implode(",", $this->input->post('clients')) : '',
				'workspace_id' => $this->session->userdata('workspace_id'),
				'start_date' => strip_tags($this->input->post('start_date', true)),
				'end_date' => strip_tags($this->input->post('end_date', true))
			);
			// $data = escape_array($data);
			//preparing activity log data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project',
				'project_id' => $this->input->post('update_id'),
				'project_title' => get_project_title($this->input->post('update_id')),
				'activity' => 'Updated',
				'message' => get_user_name() . ' Updated Project ' . get_project_title($this->input->post('update_id')),
			);
			if ($this->projects_model->edit_project($data, $this->input->post('update_id'))) {
				$this->activity_model->store_activity($activity_data);

				//preparing notfication data

				// $project_users = $this->projects_model->get_project_users($this->input->post('update_id'));

				// if (!empty($project_users)) {
				// 	$project_users = explode(",", $project_users[0]['user_id']);
				// 	if (($key = array_search($admin_id, $project_users)) !== false) {
				// 		unset($project_users[$key]);
				// 	}
				// 	$project_users = array_diff( $project_users, $new_users);
				// 	$user_ids = implode(",", $project_users);
				// 	$project = $this->projects_model->get_project_by_id($this->input->post('update_id'));
				// 	$admin = $this->users_model->get_user_by_id($admin_id);
				// 	$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				// 	$notification = $admin_name . " updated project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>.";
				// 	$title = $admin_name . " updated project <b>" . $project[0]['title'] . "</b>.";
				// 	$notification_data = array(
				// 		'user_id' => $this->session->userdata('user_id'),
				//		'workspace_id' => $this->session->userdata('workspace_id'),
				// 		'title' => $title,
				// 		'user_ids' => $user_ids,
				// 		'type' => 'project',
				// 		'type_id' => $this->input->post('update_id'),
				// 		'notification' => $notification,
				// 	);
				// 	$this->notifications_model->store_notification($notification_data);
				// }


				// print_r($new_users);
				if (!empty($new_users)) {
					$user_ids = implode(",", $new_users);
					$project = $this->projects_model->get_project_by_id($this->input->post('update_id'));
					$admin = $this->users_model->get_user_by_id($admin_id);
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$notification = $admin_name . " assigned you new project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>.";
					$title = $admin_name . " assigned you new project <b>" . $project[0]['title'] . "</b>.";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'project',
						'type_id' => $this->input->post('update_id'),
						'notification' => $notification,
					);
					$this->projects_model->send_email($new_users, $this->input->post('update_id'), $admin_id);
					$this->notifications_model->store_notification($notification_data);
				}

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

		$project_id = $this->uri->segment(3);
		if (!empty($project_id) && is_numeric($project_id)  || $project_id < 1) {
			//preparing activity data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'activity' => 'Deleted',
				'message' => get_user_name() . ' Deleted Project ' . get_project_title($project_id),
			);
			//preparing notification data

			$project_users = $this->projects_model->get_project_users($project_id);
			$admin_id = $this->session->userdata('user_id');
			if (!empty($project_users)) {
				$project_users = explode(",", $project_users[0]['user_id']);
				if (($key = array_search($admin_id, $project_users)) !== false) {
					unset($project_users[$key]);
				}

				$admin = $this->users_model->get_user_by_id($admin_id);
				$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				$user_ids = implode(",", $project_users);
				$project = $this->projects_model->get_project_by_id($project_id);
				$admin = $this->users_model->get_user_by_id($admin_id);
				$notification = $admin_name . " deleted project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>.";
				$title = $admin_name . " deleted project <b>" . $project[0]['title'] . "</b>.";
				$notification_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => $title,
					'user_ids' => $user_ids,
					'type' => 'project',
					'type_id' => $project_id,
					'notification' => $notification,
				);
			}

			if ($this->projects_model->delete_project($project_id)) {
				$this->activity_model->store_activity($activity_data);
				// if(!empty($project_users)){
				// 	$this->notifications_model->store_notification($notification_data);
				// }

				$this->session->set_flashdata('message', 'Project deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Project could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('projects', 'refresh');
	}

	public function delete_project_file()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$file_id = $this->uri->segment(3);
		if (!empty($file_id) && is_numeric($file_id)  || $file_id < 1) {
			$project_id = get_project_id_by_file_id($file_id);
			//preparing activity data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project File',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'file_id' => $file_id,
				'file_name' => get_file_name($file_id),
				'activity' => 'Deleted',
				'message' => get_user_name() . ' Deleted File ' . get_file_name($file_id),
			);
			if ($this->projects_model->delete_file($file_id)) {
				$this->activity_model->store_activity($activity_data);
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
			$project_id = $this->uri->segment(3);

			if (empty($project_id) || !is_numeric($project_id) || $project_id < 1) {
				redirect('projects', 'refresh');
				return false;
				exit(0);
			}
			$user_id = $this->session->userdata('user_id');
			$notification_id = $this->notifications_model->get_id_by_type_id($project_id, 'project', $user_id);
			if (!empty($notification_id) && isset($notification_id[0])) {
				$notification_id = $notification_id[0]['id'];
				$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
			}
			$projects = $this->projects_model->get_project_by_id($project_id);
			if (!empty($projects) && isset($projects[0])) {

				$projects_user_ids = explode(',', $projects[0]['user_id']);
				$projects_client_ids = explode(',', $projects[0]['client_id']);
				$project_users = array_merge($projects_user_ids, $projects_client_ids);

				if (in_array($user_id, $project_users) || is_admin()) {
					$data['projects'] = $projects[0];


					$data['projects']['projects_users'] = $this->users_model->get_user_array_responce($projects_user_ids);


					$data['projects']['projects_clients'] = $this->users_model->get_user_array_responce($projects_client_ids);

					$milestones = $this->milestones_model->get_milestone_by_project_id($project_id, $this->session->userdata('workspace_id'));
					$data['milestones'] = $milestones;

					$type = 'project';
					$data['files'] = $this->projects_model->get_files($project_id, $type);

					$data['todo_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'project_id=' . $project_id . ' and status="todo"');

					$data['inprogress_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'project_id=' . $project_id . ' and status="inprogress"');

					$data['review_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'project_id=' . $project_id . ' and status="review"');

					$data['done_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'project_id=' . $project_id . ' and status="done"');
					$workspace_id = $this->session->userdata('workspace_id');
					if (!empty($workspace_id)) {
						$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
						$this->load->view('project-details', $data);
					} else {
						redirect('home', 'refresh');
					}
				} else {
					$this->session->set_flashdata('message', 'You are not authorized to view this project.');
					$this->session->set_flashdata('message_type', 'error');
					redirect('projects', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'This project was deleted.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
			}
		}
	}

	public function create_milestone()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('cost', str_replace(':', '', 'cost is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$class_sts = $this->input->post('status');
			if ($class_sts == 'incomplete') {
				$class = 'danger';
			} else {
				$class = 'success';
			}
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'status' => $this->input->post('status'),
				'class' => $class,
				'cost' => $this->input->post('cost'),
				'description' => strip_tags($this->input->post('description', true)),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'project_id' => $this->uri->segment(3)
			);
			$milestone_id = $this->milestones_model->create_milestone($data);
			//preparing activity data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project Milestone',
				'project_id' => $this->uri->segment(3),
				'project_title' => get_project_title($this->uri->segment(3)),
				'milestone_id' => $milestone_id,
				'milestone' => get_milestone_title($milestone_id),
				'activity' => 'Created',
				'message' => get_user_name() . ' Created Milestone ' . get_milestone_title($milestone_id),
			);
			if ($milestone_id != false) {

				$this->activity_model->store_activity($activity_data);
				$this->session->set_flashdata('message', 'Milestone Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Milestone could not Created! Try again!');
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

	public function edit_milestone()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('status', str_replace(':', '', 'status is empty.'), 'trim|required');
		$this->form_validation->set_rules('cost', str_replace(':', '', 'cost is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$class_sts = $this->input->post('status');
			if ($class_sts == 'incomplete') {
				$class = 'danger';
			} else {
				$class = 'success';
			}

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'status' => $this->input->post('status'),
				'class' => $class,
				'cost' => $this->input->post('cost'),
				'description' => strip_tags($this->input->post('description', true))
			);
			$project_id = get_project_id_by_milestone_id($this->input->post('update_id'));
			//preparing activity data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project Milestone',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'milestone_id' => $this->input->post('update_id'),
				'milestone' => get_milestone_title($this->input->post('update_id')),
				'activity' => 'Updated',
				'message' => get_user_name() . ' Updated Milestone ' . get_milestone_title($this->input->post('update_id')),
			);
			if ($this->milestones_model->edit_milestone($data, $this->input->post('update_id'))) {
				$this->activity_model->store_activity($activity_data);
				$this->session->set_flashdata('message', 'Milestone Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Milestone could not Updated! Try again!');
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

	public function delete_milestone()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$milestone_id = $this->uri->segment(3);
		$project_id = $this->uri->segment(4);

		if (!empty($milestone_id) && is_numeric($milestone_id) && !empty($project_id) && is_numeric($project_id)) {
			//preparing activity data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Project Milestone',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'milestone_id' => $milestone_id,
				'milestone' => get_milestone_title($milestone_id),
				'activity' => 'Deleted',
				'message' => get_user_name() . ' Deleted Milestone ' . get_milestone_title($milestone_id),
			);
			if ($this->milestones_model->delete_milestone($milestone_id)) {
				$this->activity_model->store_activity($activity_data);
				$this->session->set_flashdata('message', 'Milestone deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Milestone could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('projects/details/' . $project_id, 'refresh');
	}

	public function get_milestone_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$milestone_id = $this->input->post('id');


			if (empty($milestone_id) || !is_numeric($milestone_id) || $milestone_id < 1) {
				redirect('projects', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->milestones_model->get_milestone_by_id($milestone_id);


			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}

	public function tasks($project_id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			if (!isset($project_id) || empty($project_id) || !is_numeric($project_id) || $project_id < 1) {
				redirect('tasks', 'refresh');
				return false;
				exit(0);
			}
			// 			$task_id=$this->uri->segment(4);
			$user_id = $this->session->userdata('user_id');

			$projects = $this->projects_model->get_project_by_id($project_id);
			if (!empty($projects) && isset($projects[0])) {
				// $task_users = $this->tasks_model->get_task_users($task_id);
				// $task_users = explode(',', $task_users[0]['user_id']);

				// if (in_array($user_id, $task_users) || is_admin()) {

				$notification_id = $this->notifications_model->get_id_by_type_id($project_id, 'task', $user_id);
				if (!empty($notification_id) && isset($notification_id[0])) {
					$notification_id = $notification_id[0]['id'];
					$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
				}
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
				$filter = '';
				$data['all_projects'] = $this->projects_model->get_project($this->session->userdata('workspace_id'), $this->session->userdata('user_id'), $filter);

				$projects = $this->projects_model->get_project_by_id($project_id);
				$data['current_project'] = $projects[0];

				$data['milestones'] = $this->milestones_model->get_milestone_by_project_id($project_id, $this->session->userdata('workspace_id'));

				$data['tasks'] = $tasks = $this->tasks_model->get_task_by_project_id($project_id);
				$todo = 0;
				$inprogress = 0;
				$review = 0;
				$done = 0;
				foreach ($tasks as $task) {
					if ($task['status'] == 'todo') {
						$data['todo'] = $todo = $todo + 1;
					} elseif ($task['status'] == 'inprogress') {
						$data['inprogress'] = $inprogress = $inprogress + 1;
					} elseif ($task['status'] == 'review') {
						$data['review'] = $review = $review + 1;
					} else {
						$data['done'] = $done = $done + 1;
					}
				}
				$workspace_id = $this->session->userdata('workspace_id');
				if (!empty($workspace_id)) {
					$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();

					$this->load->view('tasks', $data);
				} else {
					redirect('home', 'refresh');
				}


				// }else{
				// $this->session->set_flashdata('message', 'You are not authorized.');
				// $this->session->set_flashdata('message_type', 'error');
				// redirect('tasks', 'refresh');

				// }

			} else {
				$this->session->set_flashdata('message', 'No tasks found.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('tasks', 'refresh');
			}
		}
	}

	public function create_task()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('priority', str_replace(':', '', 'priority is empty.'), 'trim|required');
		$this->form_validation->set_rules('due_date', str_replace(':', '', 'due date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		$this->form_validation->set_rules('user_id', 'required');

		if ($this->form_validation->run() === TRUE) {
			$class_sts = $this->input->post('priority');
			if ($class_sts == 'high') {
				$class = 'danger';
			} elseif ($class_sts == 'medium') {
				$class = 'success';
			} else {
				$class = 'info';
			}
			$user_ids = (!empty($_POST['user_id'])) ? implode(",", $this->input->post('user_id')) : "";
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'priority' => $this->input->post('priority'),
				'class' => $class,
				'project_id' => $this->uri->segment(3),
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'workspace_id' => $this->session->userdata('workspace_id'),
				'milestone_id' => $this->input->post('milestone_id'),
				'due_date' => strip_tags($this->input->post('due_date', true))
			);
			$task_id = $this->tasks_model->create_tast($data);

			// preparing activity log data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Task',
				'project_id' => $this->uri->segment(3),
				'project_title' => get_project_title($this->uri->segment(3)),
				'task_id' => $task_id,
				'task_title' => strip_tags($this->input->post('title', true)),
				'activity' => 'Created',
				'message' => get_user_name() . ' Created Task ' . strip_tags($this->input->post('title', true)),
			);
			$admin_id = $this->session->userdata('user_id');
			$user_ids = $this->input->post('user_id');
			$task_title = strip_tags($this->input->post('title', true));
			if ($task_id != false) {
				$this->activity_model->store_activity($activity_data);
				if (!empty($user_ids)) {
					if (($key = array_search($admin_id, $user_ids)) !== false) {
						unset($user_ids[$key]);
					}

					//preparing notification data
					$user_ids = implode(",", $user_ids);
					$project = $this->projects_model->get_project_by_id($this->uri->segment(3));
					$admin = $this->users_model->get_user_by_id($admin_id);
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$title = $admin_name . " assigned you new task <b>" . $task_title . "</b>.";
					$notification = $admin_name . " assigned you new task - <b>" . $task_title . "</b> ID <b>#" . $task_id . "</b> , Project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'task',
						'type_id' => $this->uri->segment(3),
						'notification' => $notification,
					);
					$user_ids = explode(",", $user_ids);
					if (!empty($user_ids[0])) {
						$this->notifications_model->store_notification($notification_data);
						$this->tasks_model->send_email($user_ids, $task_id, $admin_id);
					}
				}
				$this->projects_model->project_task_count_update($this->uri->segment(3));
				$this->session->set_flashdata('message', 'Task Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Task could not Created! Try again!');
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

	public function edit_task()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('priority', str_replace(':', '', 'priority is empty.'), 'trim|required');
		$this->form_validation->set_rules('due_date', str_replace(':', '', 'due date is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		$this->form_validation->set_rules('user_id', 'required');

		if ($this->form_validation->run() === TRUE) {
			$class_sts = $this->input->post('priority');
			if ($class_sts == 'high') {
				$class = 'danger';
			} elseif ($class_sts == 'medium') {
				$class = 'success';
			} else {
				$class = 'info';
			}
			// checking for new users
			$task_users = $this->tasks_model->get_task_users($this->input->post('update_id'));
			$user_ids = (!empty($_POST['user_id'])) ? $this->input->post('user_id') : "";
			$task_users = explode(",", $task_users[0]['user_id']);
			if (!empty($user_ids)) {
				$new_users = array();
				for ($i = 0; $i < count($user_ids); $i++) {
					if (!in_array($user_ids[$i], $task_users)) {
						array_push($new_users, $user_ids[$i]);
					}
				}
			}
			$admin_id = $this->session->userdata('user_id');
			if (($key = array_search($admin_id, $new_users)) !== false) {
				unset($new_users[$key]);
			}
			$user_ids = (!empty($_POST['user_id'])) ? implode(",", $this->input->post('user_id')) : "";
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'priority' => $this->input->post('priority'),
				'class' => $class,
				'description' => strip_tags($this->input->post('description', true)),
				'user_id' => $user_ids,
				'milestone_id' => $this->input->post('milestone_id'),
				'due_date' => strip_tags($this->input->post('due_date', true))
			);
			$project_id = get_project_id_by_task_id($this->input->post('update_id'));
			//preparing activity log data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Task',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'task_id' => $this->input->post('update_id'),
				'task_title' => get_task_title($this->input->post('update_id')),
				'activity' => 'Updated',
				'message' => get_user_name() . ' Updated Task ' . get_task_title($this->input->post('update_id')),
			);
			$task_id = $this->input->post('update_id');
			$admin_id = $this->session->userdata('user_id');
			$task_title = strip_tags($this->input->post('title', true));

			if ($this->tasks_model->edit_task($data, $this->input->post('update_id'))) {
				$this->activity_model->store_activity($activity_data);
				if (!empty($new_users)) {
					//preparing notification data
					$user_ids = implode(",", $new_users);
					$project = $this->projects_model->get_project_by_id($project_id);
					$admin = $this->users_model->get_user_by_id($admin_id);
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$title = $admin_name . " assigned you new task <b>" . $task_title . "</b>.";
					$notification = $admin_name . " assigned you new task - <b>" . $task_title . "</b> ID <b>#" . $task_id . "</b> , Project - <b>" . $project[0]['title'] . "</b> ID <b>#" . $project[0]['id'] . "</b>";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'task',
						'type_id' => $project_id,
						'notification' => $notification,
					);

					$this->notifications_model->store_notification($notification_data);
					$this->tasks_model->send_email($new_users, $this->input->post('update_id'), $this->session->userdata('user_id'));
				}
				$this->session->set_flashdata('message', 'Task Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Task could not Updated! Try again!');
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

	public function delete_task()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$task_id = $this->uri->segment(3);
		$project_id = $this->uri->segment(4);
		if (!empty($task_id) && is_numeric($task_id) && !empty($project_id) && is_numeric($project_id)) {
			//preparing activity log data
			$activity_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_name' => get_user_name(),
				'type' => 'Task',
				'project_id' => $project_id,
				'project_title' => get_project_title($project_id),
				'task_id' => $task_id,
				'task_title' => get_task_title($task_id),
				'activity' => 'Deleted',
				'message' => get_user_name() . ' Deleted Task ' . get_task_title($task_id),
			);
			//preparing notification data
			$task_users = $this->tasks_model->get_task_users($task_id);
			$task_users = $task_users[0]['user_id'];
			$admin_id = $this->session->userdata('user_id');
			$admin = $this->users_model->get_user_by_id($admin_id);
			$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
			$notification = $admin_name . " deleted task - <b>" . get_task_title($task_id) . "</b> ID <b>#" . $task_id . "</b>.";
			$title = $admin_name . " deleted task <b>" . get_task_title($task_id) . "</b>.";

			$notification_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'title' => $title,
				'user_ids' => $task_users,
				'type' => 'task',
				'type_id' => $task_id,
				'notification' => $notification,
			);
			if ($this->tasks_model->delete_task($task_id)) {
				$this->activity_model->store_activity($activity_data);
				// $this->notifications_model->store_notification($notification_data);
				$this->projects_model->project_task_count_decreas($project_id);
				$this->session->set_flashdata('message', 'Task deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Task could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('projects/tasks/' . $project_id, 'refresh');
	}

	public function get_task_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$task_id = $this->input->post('id');

			if (empty($task_id) || !is_numeric($task_id) || $task_id < 1) {
				redirect('projects/tasks', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->tasks_model->get_task_by_id($task_id);

			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}

	public function task_status_update()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$id = $this->input->post('id');
		$data = array(
			'status' => $this->input->post('status')
		);
		$project_id = get_project_id_by_task_id($id);
		$activity_data = array(
			'user_id' => $this->session->userdata('user_id'),
			'workspace_id' => $this->session->userdata('workspace_id'),
			'user_name' => get_user_name(),
			'type' => 'Task Status',
			'project_id' => $project_id,
			'project_title' => get_project_title($project_id),
			'task_id' => $id,
			'task_title' => get_task_title($id),
			'activity' => 'Updated',
			'message' => get_user_name() . ' Updated Task Status For Task ' . get_task_title($id),
		);
		if ($this->tasks_model->task_status_update($data, $id)) {
			$this->activity_model->store_activity($activity_data);
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

	public function add_task_details()
	{
		$id = '';
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('workspace_id', str_replace(':', '', 'workspace_id is empty.'), 'trim|required');
		$this->form_validation->set_rules('task_id', str_replace(':', '', 'task_id is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			if (!empty($this->input->post('comment'))) {
				$data = array(
					'comment' => strip_tags($this->input->post('comment', true)),
					'workspace_id' => $this->input->post('workspace_id'),
					'project_id' => $this->input->post('project_id'),
					'task_id' => $this->input->post('task_id'),
					'user_id' => $this->session->userdata('user_id')
				);

				$id = $this->tasks_model->add_task_comment($data);
				$comment = strip_tags($this->input->post('comment', true));
				$lenght = strlen($comment);
				$comment = $lenght > 25 ? mb_substr($comment, 0, 25) . '...' : $comment;
				$activity_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'user_name' => get_user_name(),
					'type' => 'Comment',
					'project_id' => $this->input->post('project_id'),
					'project_title' => get_project_title($this->input->post('project_id')),
					'task_id' => $this->input->post('task_id'),
					'task_title' => get_task_title($this->input->post('task_id')),
					'comment_id' => $id,
					'comment' => $comment,
					'activity' => 'Created',
					'message' => get_user_name() . ' Created Comment ' . strip_tags($this->input->post('comment', true)),
				);
				$this->activity_model->store_activity($activity_data);
				$this->projects_model->project_comment_count_update($this->input->post('project_id'));
				$this->tasks_model->task_comment_count_update($this->input->post('task_id'));
			}

			if (!empty($_FILES['file']['name'])) {

				$config['upload_path']          = './assets/project/';
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
						'type' => 'task',
						'type_id' => $this->input->post('task_id')
					);
					$id = $this->projects_model->add_file($data);
				} else {
					$this->session->set_flashdata('message', 'Sorry! this type of file not allowed');
					$this->session->set_flashdata('message_type', 'error');
					$response['error'] = false;
					$response['csrfName'] = $this->security->get_csrf_token_name();
					$response['csrfHash'] = $this->security->get_csrf_hash();
					echo json_encode($response);
					return false;
				}
			}

			if ($id != false && $id != '') {
				$this->session->set_flashdata('message', 'Added successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Could not Added! Try again!');
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


	public function add_project_file()
	{
		$id = '';
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('workspace_id', str_replace(':', '', 'workspace_id is empty.'), 'trim|required');
		$this->form_validation->set_rules('project_id', str_replace(':', '', 'project_id is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			if (!empty($_FILES['file']['name'])) {
				if (!is_dir('./assets/project/')) {
					mkdir('./assets/project/', 0777, TRUE);
				}
				$config['upload_path']          = './assets/project/';
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
						'type' => 'project',
						'type_id' => $this->input->post('project_id')
					);
					$activity_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'user_name' => get_user_name(),
						'type' => 'Project File',
						'project_id' => $this->input->post('project_id'),
						'project_title' => get_project_title($this->input->post('project_id')),
						'file_id' => $id,
						'file_name' => get_file_name($id),
						'activity' => 'Uploaded',
						'message' => get_user_name() . ' Uploaded File ' . get_file_name($id),
					);
					$id = $this->projects_model->add_file($data);
					if ($id != false) {
						$this->activity_model->store_activity($activity_data);
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

	public function get_project_files()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$this->form_validation->set_rules('project_id', str_replace(':', '', 'project_id is empty.'), 'trim|required');

			if ($this->form_validation->run() === TRUE) {
				$type = 'project';
				$project_id = $this->input->post('project_id');
				$data = $this->projects_model->get_files($project_id, $type);

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
