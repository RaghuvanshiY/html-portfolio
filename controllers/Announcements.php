<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Announcements extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['announcements_model', 'workspace_model', 'notifications_model', 'users_model']);
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
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('announcements', $data);
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

		if (is_admin()) {
			$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
			$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
			if ($this->form_validation->run() === TRUE) {
				$data = array(
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => strip_tags($this->input->post('title', true)),
					'description' => $this->input->post('description'),
					'user_id' => $this->session->userdata('user_id')

				);
				$announcement_id = $this->announcements_model->create_announcement($data);

				if ($announcement_id != false) {
					// $user_ids = $this->users_model->get_user_ids($this->session->userdata('user_id'));
					$workspace_id = $this->session->userdata('workspace_id');
					$user_ids = $this->users_model->get_user_in_workspace($this->session->userdata('user_id'), $workspace_id);
					// print_r($user_ids);
					$user_ids = !empty($user_ids) ? implode(",", $user_ids) : '';
					$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
					$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
					$title = $admin_name . " posted announcement <b>" . strip_tags($this->input->post('title', true)) . "</b>.";
					$notification = $admin_name . " posted announcement - <b>" . strip_tags($this->input->post('title', true)) . "</b> ID <b>#" . $announcement_id . "</b>";
					$notification_data = array(
						'user_id' => $this->session->userdata('user_id'),
						'workspace_id' => $this->session->userdata('workspace_id'),
						'title' => $title,
						'user_ids' => $user_ids,
						'type' => 'announcement',
						'type_id' => $announcement_id,
						'notification' => $notification,
					);
					if (!empty($user_ids)) {
						$this->notifications_model->store_notification($notification_data);
					}
					$this->session->set_flashdata('message', 'Announcement Created successfully.');
					$this->session->set_flashdata('message_type', 'success');
				} else {
					$this->session->set_flashdata('message', 'Announcement could not Created! Try again!');
					$this->session->set_flashdata('message_type', 'error');
				}

				$response['error'] = false;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'Successful';
				echo json_encode($response);
				return false;
			} else {
				$response['error'] = true;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = validation_errors();
				echo json_encode($response);
				return false;
			}
		} else {
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = "You are not authorized to create announcement";
			echo json_encode($response);
		}
	}

	public function edit()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'Description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'description' => $this->input->post('description'),

			);
			$id = strip_tags($this->input->post('update_id'), true);
			if (!empty($id) && is_numeric($id)) {
				$announcement = $this->announcements_model->get_announcement_by_id($id);
				if (is_admin()) {
					if ($this->announcements_model->edit_announcement($data, $id)) {
						$workspace_id = $this->session->userdata('workspace_id');
						$user_ids = $this->users_model->get_user_in_workspace($this->session->userdata('user_id'), $workspace_id);
						$user_ids = !empty($user_ids) ? implode(",", $user_ids) : '';
						$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
						$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
						$title = $admin_name . " updated announcement <b>" . $announcement[0]['title'] . "</b>.";
						$notification = $admin_name . " updated announcement - <b>" . $announcement[0]['title'] . "</b> ID <b>#" . $id . "</b>";
						$notification_data = array(
							'user_id' => $this->session->userdata('user_id'),
							'workspace_id' => $this->session->userdata('workspace_id'),
							'title' => $title,
							'user_ids' => $user_ids,
							'type' => 'announcement',
							'type_id' => $id,
							'notification' => $notification,
						);
						if (!empty($user_ids)) {
							$this->notifications_model->store_notification($notification_data);
						}
						$this->session->set_flashdata('message', 'Announcement Updated successfully.');
						$this->session->set_flashdata('message_type', 'success');
					} else {
						$this->session->set_flashdata('message', 'Announcement could not Updated! Try again!');
						$this->session->set_flashdata('message_type', 'error');
					}
				} else {
					$this->session->set_flashdata('message', 'You are not authorized to update announcements');
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

	public function delete()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$announcement_id = $this->uri->segment(3);
		if (!empty($announcement_id) && is_numeric($announcement_id)) {
			$announcement = $this->announcements_model->get_announcement_by_id($announcement_id);
			if (is_admin()) {

				$user_ids = $this->users_model->get_user_ids($this->session->userdata('user_id'));
				$user_ids = !empty($user_ids) ? implode(",", $user_ids) : '';
				$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
				$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				$title = $admin_name . " deleted announcement <b>" . $announcement[0]['title'] . "</b>.";
				$notification = $admin_name . " deleted announcement - <b>" . $announcement[0]['title'] . "</b> ID <b>#" . $announcement_id . "</b>";
				$notification_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => $title,
					'user_ids' => $user_ids,
					'type' => 'announcement',
					'type_id' => $announcement_id,
					'notification' => $notification,
				);
				if (!empty($announcement_id) && is_numeric($announcement_id)) {
					if ($this->announcements_model->delete_announcement($announcement_id)) {
						// if (!empty($user_ids)) {
						// 	$this->notifications_model->store_notification($notification_data);
						// }

						$this->session->set_flashdata('message', 'Announcement deleted successfully.');
						$this->session->set_flashdata('message_type', 'success');
						$response['error'] = false;
						$response['message'] = 'Announcement deleted successfully';
						echo json_encode($response);
					} else {
						$this->session->set_flashdata('message', 'Announcement could not be deleted! Try again!');
						$this->session->set_flashdata('message_type', 'error');
						$response['error'] = true;
						$response['message'] = 'Announcement could not be deleted! Try again!';
						echo json_encode($response);
					}
				}
				redirect('announcements', 'refresh');
			} else {
				$response['error'] = true;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'You are not authorized to delete announcement.';
				echo json_encode($response);
			}
		}
	}

	public function pin()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$announcement_id = $this->uri->segment(3);

		if (!empty($announcement_id) && is_numeric($announcement_id)) {
			if (is_admin()) {
				if ($this->announcements_model->make_announcement_pinned($announcement_id)) {
					$this->session->set_flashdata('message', 'Announcement pinned successfully.');
					$this->session->set_flashdata('message_type', 'success');
					$response['error'] = false;
					$response['message'] = 'Announcement pinned successfully';
				} else {
					$this->session->set_flashdata('message', 'Announcement could not be pinned! Try again!');
					$this->session->set_flashdata('message_type', 'error');
					$response['error'] = true;
					$response['message'] = 'Announcement could not be pinned! Try again!';
				}
				echo json_encode($response);
			} else {
				$response['error'] = true;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'You are not authorized to pin announcements';
				echo json_encode($response);
			}
		}
	}

	public function unpin()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$announcement_id = $this->uri->segment(3);

		if (!empty($announcement_id) && is_numeric($announcement_id)) {
			if (is_admin()) {
				if ($this->announcements_model->make_announcement_unpinned($announcement_id)) {
					$this->session->set_flashdata('message', 'Announcement un-pinned successfully.');
					$this->session->set_flashdata('message_type', 'success');
					$response['error'] = false;
					$response['message'] = 'Announcement un-pinned successfully';
				} else {
					$this->session->set_flashdata('message', 'Announcement could not be un-pinned! Try again!');
					$this->session->set_flashdata('message_type', 'error');
					$response['error'] = true;
					$response['message'] = 'Announcement could not be un-pinned! Try again!';
				}
				echo json_encode($response);
			} else {
				$response['error'] = true;

				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'You are not authorized to un-pin announcements';
				echo json_encode($response);
			}
		}
	}


	public function get_announcements_list()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			return $this->announcements_model->get_announcements_list($workspace_id);
		}
	}

	public function get_announcement_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$announcement_id = $this->input->post('id');

			if (empty($announcement_id) || !is_numeric($announcement_id)) {
				redirect('announcements', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->announcements_model->get_announcement_by_id($announcement_id);


			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
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
			$announcement_id = $this->uri->segment(3);

			if (empty($announcement_id) || !is_numeric($announcement_id) || $announcement_id < 1) {
				redirect('home', 'refresh');
				return false;
				exit(0);
			}
			$user_id = $this->session->userdata('user_id');
			$notification_id = $this->notifications_model->get_id_by_type_id($announcement_id, 'announcement', $user_id);
			if (!empty($notification_id) && isset($notification_id[0])) {
				$notification_id = $notification_id[0]['id'];
				$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
			}
			$announcement = $this->announcements_model->get_announcement_by_id($announcement_id);
			// print_r($notification[0]['user_name']);
			if (empty($announcement) || !isset($announcement[0])) {
				$this->session->set_flashdata('message', 'This announcement was deleted.');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit(0);
			}
			$user = $this->users_model->get_user_by_id($announcement[0]['user_id']);
			$data['user_name'] = $user[0]['first_name'] . " " . $user[0]['last_name'];
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$data['announcement'] = $announcement;
				$this->load->view('announcement-details', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}
}
