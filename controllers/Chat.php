<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chat extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		// $this->db->query("SET time_zone = '+05:30'");
		$this->load->model(['users_model', 'workspace_model', 'chat_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');
		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');
	}

	public function make_me_online()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$user_id = $this->session->userdata('user_id');
			$date = strtotime('now');
			$date = $date + 60;
			$data = array(
				'last_online' => $date
			);

			if ($this->chat_model->make_me_online($user_id, $data)) {

				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}
	public function get_system_settings()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$response = get_system_settings('web_fcm_settings');
			echo json_encode($response);
		}
	}

	public function super_admin_make_group_admin()
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
			redirect('auth', 'refresh');
		} else {
			$group_id = $this->uri->segment(3);

			if (empty($group_id) || !is_numeric($group_id) || $group_id < 1) {
				redirect('chat', 'refresh');
				return false;
				exit(0);
			}

			$data = array(
				'group_id' => $group_id,
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'is_admin' => 1
			);

			$this->chat_model->add_group_members($data);
			redirect('chat', 'refresh');
		}
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
			$workspace_id = $this->session->userdata('workspace_id');
			$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
			$admin_ids = explode(',', $current_workspace_id[0]->admin_id);
			$section = array_map('trim', $admin_ids);
			$data['admin_ids'] = $admin_ids = $section;
			$members = $this->chat_model->get_members($user_ids);
			$member = array();
			$i = 0;

			$workspace_id = $this->session->userdata('workspace_id');
			$type = 'person';
			$to_id = $this->session->userdata('user_id');

			foreach ($members as $row) {

				$from_id = $row['id'];

				$unread_meg = $this->chat_model->get_unread_msg_count($type, $from_id, $to_id, $workspace_id);

				$member[$i] = $row;
				$member[$i]['unread_msg'] = $unread_meg;
				$member[$i]['picture']  = mb_substr($row['first_name'], 0, 1) . '' . mb_substr($row['last_name'], 0, 1);

				$date = strtotime('now');
				if ($to_id == $row['id']) {
					$member[$i]['is_online'] = 1;
				} else {
					if ($row['last_online'] > $date) {
						$member[$i]['is_online'] = 1;
					} else {
						$member[$i]['is_online'] = 0;
					}
				}
				$i++;
			}

			if ($this->ion_auth->is_admin()) {
				$data['not_in_groups'] = $this->chat_model->get_groups_all($to_id, $workspace_id);
			} else {
				$data['not_in_groups'] = '';
			}

			$data['groups'] = $this->chat_model->get_groups($to_id, $workspace_id);
			$data['members'] = $member;
			$this->load->view('chat', $data);
		}
	}


	public function get_online_members()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {


			$user_id = $this->session->userdata('user_id');
			$date = strtotime('now');
			$date = $date + 15;
			$data = array(
				'last_online' => $date
			);

			$this->chat_model->make_me_online($user_id, $data);

			$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;

			$members = $this->chat_model->get_members($user_ids);
			$member = array();
			$i = 0;

			$workspace_id = $this->session->userdata('workspace_id');
			$type = 'person';
			$to_id = $this->session->userdata('user_id');

			foreach ($members as $row) {

				$from_id = $row['id'];

				$unread_meg = $this->chat_model->get_unread_msg_count($type, $from_id, $to_id, $workspace_id);

				$member[$i] = $row;
				$member[$i]['unread_msg'] = $unread_meg;
				$member[$i]['picture']  = mb_substr($row['first_name'], 0, 1) . '' . mb_substr($row['last_name'], 0, 1);
				$date = strtotime('now');

				if ($row['last_online'] > $date) {
					$member[$i]['is_online'] = 1;
				} else {
					$member[$i]['is_online'] = 0;
				}
				$i++;
			}

			$data1['groups'] = $this->chat_model->get_groups($to_id, $workspace_id);
			$data1['members'] = $member;

			if (!empty($member)) {
				$response['error'] = false;
				$response['data'] = $data1;
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}


	public function edit_group()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('update_id', str_replace(':', '', 'ID is empty.'), 'trim|required');
		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$admin_id = $this->session->userdata('user_id');
			$group_id = $this->input->post('update_id');

			if (!empty($this->input->post('users'))) {
				$group_mem_ids = implode(",", $this->input->post('users')) . ',' . $admin_id;
				$group_mem_ids = explode(",", $group_mem_ids);
			} else {
				$group_mem_ids = array($this->session->userdata('user_id'));
			}

			$no_of_mem = count($group_mem_ids);

			if (!empty($this->input->post('admins'))) {
				$admins_ids = implode(",", $this->input->post('admins')) . ',' . $admin_id;
				$admins_ids = explode(",", $admins_ids);
			} else {
				$admins_ids = array($this->session->userdata('user_id'));
			}

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'no_of_members' => $no_of_mem
			);

			if ($this->chat_model->edit_group($data, $group_id)) {

				foreach ($group_mem_ids as $user_id) {
					$data1 = array(
						'group_id' => $group_id,
						'user_id' => $user_id,
						'workspace_id' => $this->session->userdata('workspace_id')
					);

					$this->chat_model->add_group_members($data1);
				}

				$this->chat_model->remove_all_group_members($group_id, $group_mem_ids);

				$this->chat_model->make_group_admin($group_id, $admins_ids);

				$this->session->set_flashdata('message', 'Group Edited successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Group could not Edited! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}

			$response['error'] = false;
			$response['message'] = 'Successful';
			echo json_encode($response);
		} else {
			$response['error'] = true;
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}

	public function create_group()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$admin_id = $this->session->userdata('user_id');

			if (!empty($this->input->post('users'))) {
				$group_mem_ids = implode(",", $this->input->post('users')) . ',' . $admin_id;
				$group_mem_ids = explode(",", $group_mem_ids);
			} else {
				$group_mem_ids = array($this->session->userdata('user_id'));
			}


			$no_of_mem = count($group_mem_ids);

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'created_by' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'no_of_members' => $no_of_mem
			);

			$group_id = $this->chat_model->create_group($data);

			if ($group_id != false) {

				foreach ($group_mem_ids as $user_id) {
					$data1 = array(
						'group_id' => $group_id,
						'user_id' => $user_id,
						'workspace_id' => $this->session->userdata('workspace_id')
					);
					$this->chat_model->add_group_members($data1);
				}
				$admins_ids = array($admin_id);
				$this->chat_model->make_group_admin($group_id, $admins_ids);

				$this->session->set_flashdata('message', 'Group Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Group could not Created! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}

			$response['error'] = false;
			$response['message'] = 'Successful';
			echo json_encode($response);
		} else {
			$response['error'] = true;
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}


	public function update_web_fcm()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$fcm = $this->input->post('web_fcm');
			$user_id = $this->session->userdata('user_id');
			if ($this->chat_model->update_web_fcm($user_id, $fcm)) {

				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function get_group_members()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$group_id = $this->input->post('group_id');
			$users = $this->chat_model->get_group_members($group_id);
			if (!empty($users)) {

				$response['error'] = false;
				$response['data'] = $users;
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function send_msg()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$data = array(
				'workspace_id' => $this->session->userdata('workspace_id'),
				'type' => $this->input->post('chat_type'),
				'from_id' => $this->session->userdata('user_id'),
				'to_id' => $this->input->post('opposite_user_id'),
				'message' => $this->input->post('chat-input-textarea')
			);
			$msg_id = $this->chat_model->send_msg($data);

			if (!empty($_FILES['file']['name'])) {
				if (!is_dir('./assets/chats/')) {
					mkdir('./assets/chats/', 0777, TRUE);
				}
				$config['upload_path']          = './assets/chats/';
				$config['allowed_types']        = $this->config->item('allowed_types');
				$config['overwrite']            = false;
				$config['max_size']             = 10000;
				$config['max_width']            = 0;
				$config['max_height']           = 0;

				$this->load->library('upload', $config);

				$files = $_FILES;
				for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
					$_FILES['userfile']['name'] = $files['file']['name'][$i];
					$_FILES['userfile']['type'] = $files['file']['type'][$i];
					$_FILES['userfile']['tmp_name'] = $files['file']['tmp_name'][$i];
					$_FILES['userfile']['error'] = $files['file']['error'][$i];
					$_FILES['userfile']['size'] = $files['file']['size'][$i];

					$this->upload->initialize($config);

					if ($this->upload->do_upload()) {
						$file_data = $this->upload->data();
						$data = array(
							'original_file_name' => $file_data['orig_name'],
							'file_name' => $file_data['file_name'],
							'file_extension' => $file_data['file_ext'],
							'file_size' => $this->custom_funcation_model->format_size_units($file_data['file_size']),
							'user_id' => $this->session->userdata('user_id'),
							'workspace_id' => $this->session->userdata('workspace_id'),
							'message_id' => $msg_id
						);

						$file_id = $this->chat_model->add_file($data);
						$this->chat_model->add_media_ids_to_msg($msg_id, $file_id);
					}
				}
			}

			$messages = $this->chat_model->get_msg_by_id($msg_id, $this->input->post('opposite_user_id'), $this->session->userdata('user_id'), $this->input->post('chat_type'));
			$message = array();
			$i = 0;
			foreach ($messages as $row) {
				$message[$i] = $row;
				$media_files = $this->chat_model->get_media($row['id']);
				$message[$i]['media_files'] = !empty($media_files) ? $media_files : '';
				$message[$i]['text'] = $row['message'];
				$i++;
			}
			$new_msg = $message;

			if (!empty($msg_id)) {
				$to_id = $this->input->post('opposite_user_id');
				$from_id = $this->session->userdata('user_id');

				if ($to_id == $from_id && $this->input->post('chat_type') == 'person') {
					return false;
				}

				// single user msg
				if ($this->input->post('chat_type') == 'person') {

					// this is the user who going to recive FCM msg
					$user = $this->users_model->get_user_by_id($to_id);

					// this is the user who going to send FCM msg 
					$senders_info = $this->users_model->get_user_by_id($this->session->userdata('user_id'));

					$data = $notification = array();
					$notification['title'] = $senders_info[0]['first_name'] . ' ' . $senders_info[0]['last_name'];
					$notification['picture'] = mb_substr($senders_info[0]['first_name'], 0, 1) . '' . mb_substr($senders_info[0]['last_name'], 0, 1);

					$notification['profile'] = !empty($senders_info[0]['profile']) ? $senders_info[0]['profile'] : '';

					$notification['senders_name'] = $senders_info[0]['first_name'] . ' ' . $senders_info[0]['last_name'];

					$notification['type'] = 'message';
					$notification['message_type'] = 'person';
					$notification['from_id'] = $from_id;
					$notification['to_id'] = $to_id;
					$notification['msg_id'] = $msg_id;
					$notification['new_msg'] = json_encode($new_msg);
					$notification['body'] = $this->input->post('chat-input-textarea');
					$notification['icon'] = 'assets/icons/' . (!empty(get_half_logo()) ? get_half_logo() : 'logo-half.png');
					$notification['base_url'] = base_url('chat');
					$data['data']['data'] = $notification;
					$data['data']['webpush']['fcm_options']['link'] = base_url('chat');
					$data['to'] = $user[0]['web_fcm'];

					$ch = curl_init();
					$fcm_key = get_system_settings('web_fcm_settings');

					$fcm_key = !empty($fcm_key) ? json_decode($fcm_key[0]['data']) : '';

					$fcm_key = !empty($fcm_key->fcm_server_key) ? $fcm_key->fcm_server_key : '';

					curl_setopt($ch, CURLOPT_POST, 1);
					$headers = array();
					$headers[] = "Authorization: key = " . $fcm_key;
					$headers[] = "Content-Type: application/json";
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

					$result['error'] = false;
					$result['response'] = curl_exec($ch);
					if (curl_errno($ch))
						echo 'Error:' . curl_error($ch);

					curl_close($ch);
				} else {

					// group user msg
					$group_id = $this->input->post('opposite_user_id');

					$users = $this->chat_model->get_group_members($group_id);
					foreach ($users as $user) {
						$userdata = $this->users_model->get_user_by_id($user['user_id']);
						if ($user['user_id'] != $this->session->userdata('user_id')) {
							$fcm_ids[] = $userdata[0]['web_fcm'];
						}
					}

					$registrationIDs = $fcm_ids;

					// this is the user who going to send FCM msg
					$senders_info = $this->users_model->get_user_by_id($this->session->userdata('user_id'));

					$data = $notification = array();
					$notification['title'] = '#' . $users[0]['title'] . ' - ' . $senders_info[0]['first_name'] . ' ' . $senders_info[0]['last_name'];
					$notification['picture'] = mb_substr($senders_info[0]['first_name'], 0, 1) . '' . mb_substr($senders_info[0]['last_name'], 0, 1);

					$notification['profile'] = !empty($senders_info[0]['profile']) ? $senders_info[0]['profile'] : '';

					$notification['senders_name'] = $senders_info[0]['first_name'] . ' ' . $senders_info[0]['last_name'];
					$notification['type'] = 'message';
					$notification['message_type'] = 'group';
					$notification['from_id'] = $from_id;
					$notification['to_id'] = $group_id;
					$notification['msg_id'] = $msg_id;
					$notification['registrationIDs'] = $registrationIDs;
					$notification['new_msg'] = json_encode($new_msg);
					$notification['body'] = $this->input->post('chat-input-textarea');
					$notification['icon'] = 'assets/icons/' . (!empty(get_half_logo()) ? get_half_logo() : 'logo-half.png');
					$notification['base_url'] = base_url('chat');
					$data['data']['data'] = $notification;
					$data['data']['webpush']['fcm_options']['link'] = base_url('chat');
					$data['registration_ids'] = $registrationIDs;

					$ch = curl_init();
					$fcm_key = get_system_settings('web_fcm_settings');

					$fcm_key = !empty($fcm_key) ? json_decode($fcm_key[0]['data']) : '';

					$fcm_key = !empty($fcm_key->fcm_server_key) ? $fcm_key->fcm_server_key : '';

					curl_setopt($ch, CURLOPT_POST, 1);
					$headers = array();
					$headers[] = "Authorization: key = " . $fcm_key;

					$headers[] = "Content-Type: application/json";
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

					$result['error'] = false;

					$this->chat_model->set_group_msg_as_unread($group_id, $this->session->userdata('user_id'), $this->session->userdata('workspace_id'));

					$result['response'] = curl_exec($ch);
					if (curl_errno($ch))
						echo 'Error:' . curl_error($ch);

					curl_close($ch);
				}

				$response['error'] = false;
				$response['message'] = 'Successful';
				$response['msg_id'] = $msg_id;
				$response['new_msg'] = $new_msg;
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function mark_msg_read()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$workspace_id = $this->session->userdata('workspace_id');
			$type = $this->input->post('type');
			$to_id = $this->session->userdata('user_id');
			$from_id = $this->input->post('from_id');
			if ($this->chat_model->mark_msg_read($type, $from_id, $to_id, $workspace_id)) {
				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function delete_group()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = $this->session->userdata('user_id');
			$group_id = $this->uri->segment(3);

			if (empty($group_id) || !is_numeric($group_id) || $group_id < 1) {
				redirect('chat', 'refresh');
				return false;
				exit(0);
			}

			if ($this->chat_model->delete_group($group_id, $user_id, $workspace_id)) {
				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function delete_msg()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$workspace_id = $this->session->userdata('workspace_id');
			$from_id = $this->session->userdata('user_id');
			$msg_id = $this->uri->segment(3);

			if (empty($msg_id) || !is_numeric($msg_id) || $msg_id < 1) {
				redirect('chat', 'refresh');
				return false;
				exit(0);
			}

			if ($this->chat_model->delete_msg($from_id, $msg_id, $workspace_id)) {
				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function get_msg_by_id($msg_id)
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$messages = $this->chat_model->get_msg_by_id($msg_id);
			$message = array();
			$i = 0;
			foreach ($messages as $row) {
				$message[$i] = $row;
				$media_files = $this->chat_model->get_media($row['id']);
				$message[$i]['media_files'] = !empty($media_files) ? $media_files : '';
				$message[$i]['text'] = $row['message'];
				$message[$i]['position'] = 'right';
				$i++;
			}
			return $message;
		}
	}

	public function load_chat()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$workspace_id = $this->session->userdata('workspace_id');
			$type = $this->input->post('type');
			$to_id = $this->session->userdata('user_id');
			$from_id = $this->input->post('from_id');

			$offset = (!empty($_POST['offset'])) ? $this->input->post('offset') : 0;
			$limit = (!empty($_POST['limit'])) ? $this->input->post('limit') : 10;

			$sort = (!empty($_POST['sort'])) ? $this->input->post('sort') : 'id';
			$order = (!empty($_POST['order'])) ? $this->input->post('order') : 'DESC';

			$search = (!empty($_POST['search'])) ? $this->input->post('search') : '';

			$message = array();

			$messages = $this->chat_model->load_chat($type, $from_id, $to_id, $workspace_id, $offset, $limit, $sort, $order, $search);
			if ($messages['total_msg'] == 0) {

				$message['error'] = true;
				$message['error_msg'] = 'No Chat OR Msg Found';
				print_r(json_encode($message));
				return false;
			}

			$i = 0;
			$message['total_msg'] = $messages['total_msg'];
			foreach ($messages['msg'] as $row) {
				$message['msg'][$i] = $row;
				$media_files = $this->chat_model->get_media($row['id']);
				$message['msg'][$i]['media_files'] = !empty($media_files) ? $media_files : '';
				$message['msg'][$i]['text'] = $row['message'];
				if ($row['from_id'] == $to_id) {
					$message['msg'][$i]['position'] = 'right';
				} else {
					$message['msg'][$i]['position'] = 'left';
				}
				$i++;
			}
			print_r(json_encode($message));
		}
	}

	public function switch_chat()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$workspace_id = $this->session->userdata('workspace_id');
			$type = $this->input->post('type');
			$id = $this->input->post('from_id');
			$members = $this->chat_model->switch_chat($id, $type);
			$member = array();
			$i = 0;
			foreach ($members as $row) {

				$member[$i] = $row;
				if ($type == 'person') {
					$member[$i]['picture'] = mb_substr($row['first_name'], 0, 1) . '' . mb_substr($row['last_name'], 0, 1);

					$date = strtotime('now');

					if ($row['last_online'] > $date) {
						$member[$i]['is_online'] = 1;
					} else {
						$member[$i]['is_online'] = 0;
					}
				} else {
					$member[$i]['picture'] = '#';

					if ($this->chat_model->check_group_admin($row['id'], $this->session->userdata('user_id'))) {
						$member[$i]['is_admin'] = true;
					} else {
						$member[$i]['is_admin'] = false;
					}
				}

				$i++;
			}
			print_r(json_encode($member));
		}
	}

	public function get_user_picture()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$user_id = $this->uri->segment(3);
			if (empty($user_id) || !is_numeric($user_id) || $user_id < 1) {
				redirect('projects', 'refresh');
				return false;
				exit(0);
			}
			$members = $this->chat_model->get_user_picture($user_id);
			echo $members;
		}
	}

	public function send_fcm()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$to_id = $this->input->post('receiver_id');
			$from_id = $this->session->userdata('user_id');

			if ($to_id == $from_id) {
				return false;
			}

			$title = $this->input->post('title');
			$type = $this->input->post('type');
			$msg = $this->input->post('msg');
			$user = $this->users_model->get_user_by_id($to_id);

			$message_type = !empty($this->input->post('message_type')) ? $this->input->post('message_type') : 'other';

			$data = $notification = array();
			$notification['title'] = $user[0]['first_name'] . ' ' . $user[0]['last_name'];
			$notification['type'] = $type;
			$notification['message_type'] = $message_type;
			$notification['from_id'] = $from_id;
			$notification['to_id'] = $to_id;
			$notification['body'] = $msg;
			$notification['icon'] = 'assets/icons/' . (!empty(get_half_logo()) ? get_half_logo() : 'logo-half.png');
			$notification['base_url'] = base_url('chat');
			$data['data']['data'] = $notification;
			if ($type == 'message') {
				$data['priority'] = 'high';
				$data['data']['webpush']['headers']['Urgency'] = 'high';
			} else {
				$data['priority'] = 'normal';
				$data['data']['webpush']['headers']['Urgency'] = 'normal';
			}
			$data['data']['webpush']['fcm_options']['link'] = base_url('chat');
			$data['to'] = $user[0]['web_fcm'];

			$ch = curl_init();

			$fcm_key = get_system_settings('web_fcm_settings');

			$fcm_key = !empty($fcm_key) ? json_decode($fcm_key[0]['data']) : '';

			$fcm_key = !empty($fcm_key->fcm_server_key) ? $fcm_key->fcm_server_key : '';

			curl_setopt($ch, CURLOPT_POST, 1);
			$headers = array();
			$headers[] = "Authorization: key = " . (!empty($fcm_key) ? $fcm_key : '');
			$headers[] = "Content-Type: application/json";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

			$result['error'] = false;
			$result['response'] = curl_exec($ch);
			if (curl_errno($ch))
				echo 'Error:' . curl_error($ch);

			curl_close($ch);

			print_r(json_encode($result, 1));
		}
	}
}
