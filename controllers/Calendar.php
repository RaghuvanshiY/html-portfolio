<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Calendar extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['events_model', 'workspace_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
	}
	public  function _remap($method, $params = array())
	{
		$methodToCall = method_exists($this, $method) ? $method : 'index';
		return call_user_func_array(array($this, $methodToCall), $params);
	}
	public function index()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			if (!is_client()) {
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
				$user_id = $this->session->userdata('user_id');
				$event_id = !empty($this->uri->segment(2)) ? $this->uri->segment(2) : '';
				if (!empty($event_id)) {
					$notification_id = $this->notifications_model->get_id_by_type_id($event_id, 'event', $user_id);
					if (!empty($notification_id) && isset($notification_id[0])) {
						$notification_id = $notification_id[0]['id'];
						$this->notifications_model->mark_notification_as_read($notification_id, $this->session->userdata('user_id'));
					}

					$event = $this->events_model->get_event_by_id($event_id);
					if (!empty($event) && isset($event[0])) {

						$workspace_id = $this->session->userdata('workspace_id');
						$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
						if (!empty($workspace_id)) {
							$event_data = $this->events_model->fetch_events($this->session->userdata['user_id'], $workspace_id);
							$data['events'] =  $event_data;
							$this->load->view('events', $data);
							return false;
							exit();
						} else {
							redirect('home', 'refresh');
						}
					} else {
						$this->session->set_flashdata('message', 'This event was deleted!');
						$this->session->set_flashdata('message_type', 'error');
						redirect('home', 'refresh');
					}
				}
				$workspace_id = $this->session->userdata('workspace_id');
				if (!empty($workspace_id)) {
					$data['notifications'] = $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id);
					$event_data = $this->events_model->fetch_events($this->session->userdata['user_id'], $workspace_id);
					$data['events'] =  $event_data;
					$this->load->view('events', $data);
				} else {
					redirect('home', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'You are not authorized to view this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
			}
		}
	}
	public function lists()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			if (!is_client()) {
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
					$this->load->view('events-list', $data);
				} else {
					redirect('home', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'You are not authorized to view this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
			}
		}
	}
	public function get_events_list()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = $this->session->userdata('user_id');
			return $this->events_model->get_events_list($user_id, $workspace_id);
		}
	}

	public function create()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$start_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('start_date', true))));
		$end_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('end_date', true))));

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		if ($this->form_validation->run() === TRUE) {
			if ($end_date < $start_date) {
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'End date should not be lesser then start date.';
				echo json_encode($response);
				return false;
			}
			$is_public = $this->input->post('is_public');
			if (isset($is_public)) {
				$is_public = 1;
			} else {
				$is_public = 0;
			}
			$data = array(
				'workspace_id' => $this->session->userdata('workspace_id'),
				'title' => strip_tags($this->input->post('title', true)),
				'from_date' => $start_date,
				'to_date' => $end_date,
				'text_color' => strip_tags($this->input->post('text_color', true)),
				'bg_color' => strip_tags($this->input->post('background_color', true)),
				'user_id' => $this->session->userdata('user_id'),
				'is_public' => $is_public

			);
			$event_id = $this->events_model->create_event($data);

			if ($event_id != false) {
				$workspace_id = $this->session->userdata('workspace_id');
				$user_ids = $this->users_model->get_user_in_workspace($this->session->userdata('user_id'), $workspace_id);
				$client_ids = $this->users_model->get_all_client_ids(3);
				$user_ids = array_diff($user_ids, $client_ids);
				$user_ids = implode(",", $user_ids);
				$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
				$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				$title = $admin_name . " created event <b>" . strip_tags($this->input->post('title', true)) . "</b>.";
				$notification = $admin_name . " created event - <b>" . strip_tags($this->input->post('title', true)) . "</b> ID <b>#" . $event_id . "</b>";

				$notification_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => $title,
					'user_ids' => $user_ids,
					'type' => 'event',
					'type_id' => $event_id,
					'notification' => $notification,
				);
				if ($is_public == 1 && !empty($user_ids)) {
					$this->notifications_model->store_notification($notification_data);
				}
				$this->session->set_flashdata('message', 'Event Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Event could not Created! Try again!');
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
	}

	public function edit()
	{


		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		$start_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('start_date', true))));
		$end_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('end_date', true))));
		// 		echo $start_date;

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			if ($end_date < $start_date) {
				$response['error'] = true;
				$response['csrfName'] = $this->security->get_csrf_token_name();
				$response['csrfHash'] = $this->security->get_csrf_hash();
				$response['message'] = 'End date should not be lesser then start date.';
				echo json_encode($response);
				return false;
			}
			$is_public = $this->input->post('is_public');
			if (isset($is_public)) {
				$is_public = 1;
			} else {
				$is_public = 0;
			}
			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'from_date' => $start_date,
				'to_date' => $end_date,
				'text_color' => strip_tags($this->input->post('text_color', true)),
				'bg_color' => strip_tags($this->input->post('background_color', true)),
				'is_public' => $is_public

			);
			$event_id = $this->input->post('update_id');
			$event = $this->events_model->get_event_by_id($event_id);
			$result = $this->events_model->edit_event($data, $this->input->post('update_id'), $_SESSION['user_id']);
			if ($result == 1) {
				$workspace_id = $this->session->userdata('workspace_id');
				$user_ids = $this->users_model->get_user_in_workspace($this->session->userdata('user_id'), $workspace_id);
				$client_ids = $this->users_model->get_all_client_ids(3);
				$user_ids = array_diff($user_ids, $client_ids);
				$user_ids = implode(",", $user_ids);
				$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
				$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				$title = $admin_name . " updated event <b>" . $event[0]['title'] . "</b>.";
				$notification = $admin_name . " updated event - <b>" . $event[0]['title'] . "</b> ID <b>#" . $this->input->post('update_id') . "</b>";
				$notification_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => $title,
					'user_ids' => $user_ids,
					'type' => 'event',
					'type_id' => $event_id,
					'notification' => $notification,
				);
				if ($is_public == 1 && !empty($user_ids)) {
					$this->notifications_model->store_notification($notification_data);
				}
				$this->session->set_flashdata('message', 'Event Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} elseif ($result == 2) {
				$this->session->set_flashdata('message', 'You are not authorized to update this event');
				$this->session->set_flashdata('message_type', 'error');
			} elseif ($result == 0) {
				$this->session->set_flashdata('message', 'Event could not Updated! Try again!');
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

	public function get_event_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$event_id = $this->input->post('id');

			if (empty($event_id) || !is_numeric($event_id)) {
				redirect('events', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->events_model->get_event_by_id($event_id);
			$data[0]['from_date'] = date('d-M-Y H:i:s', strtotime($data[0]['from_date']));
			$data[0]['to_date'] = date('d-M-Y H:i:s', strtotime($data[0]['to_date']));

			$data[0]['title'] = stripslashes($data[0]['title']);
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}

	public function drag()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$start_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('start_date', true))));
		$end_date = date('Y-m-d H:i:s', strtotime(strip_tags($this->input->post('end_date', true))));
		$data = array(
			'from_date' => $start_date,
			'to_date' => $end_date

		);
		$event = $this->events_model->get_event_by_id($this->input->post('id'));
		$result = $this->events_model->edit_event($data, $this->input->post('id'), $_SESSION['user_id']);
		if ($result == 1) {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_ids = $this->users_model->get_user_in_workspace($this->session->userdata('user_id'), $workspace_id);

			$client_ids = $this->users_model->get_all_client_ids(3);
			$user_ids = array_diff($user_ids, $client_ids);
			$user_ids = implode(",", $user_ids);

			$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
			$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
			$title = $admin_name . " updated event <b>" . $event[0]['title'] . "</b>.";
			$notification = $admin_name . " updated event - <b>" . $event[0]['title'] . "</b> ID <b>#" . $this->input->post('update_id') . "</b>";

			$notification_data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'title' => $title,
				'user_ids' => $user_ids,
				'type' => 'event',
				'type_id' => $this->input->post('id'),
				'notification' => $notification,
			);
			if ($event[0]['is_public'] == 1 && !empty($user_ids)) {
				$this->notifications_model->store_notification($notification_data);
			}
			$this->session->set_flashdata('message', 'Event Updated successfully.');
			$this->session->set_flashdata('message_type', 'success');
		} elseif ($result == 2) {
			$this->session->set_flashdata('message', 'You are not authorized to update this event');
			$this->session->set_flashdata('message_type', 'error');
		} elseif ($result == 0) {
			$this->session->set_flashdata('message', 'Event could not Updated! Try again!');
			$this->session->set_flashdata('message_type', 'error');
		}


		$response['error'] = false;

		$response['csrfName'] = $this->security->get_csrf_token_name();
		$response['csrfHash'] = $this->security->get_csrf_hash();
		$response['message'] = 'Successful';
		echo json_encode($response);
	}

	public function delete()
	{

		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		// $event_id = $this->post->('event_id');
		$event_id = $this->uri->segment(3);
		$user_id = $_SESSION['user_id'];

		if (!empty($event_id) && is_numeric($event_id)) {
			$event = $this->events_model->get_event_by_id($event_id);
			$result = $this->events_model->delete_event($event_id, $user_id);
			if ($result == 1) {
				$user_ids = $this->users_model->get_user_ids($this->session->userdata('user_id'));
				$user_ids = $event[0]['is_public'] == 1 ? implode(",", $user_ids) : $this->session->userdata('user_id');
				$admin = $this->users_model->get_user_by_id($this->session->userdata('user_id'));
				$admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
				$title = $admin_name . " deleted event <b>" . $event[0]['title'] . "</b>.";
				$notification = $admin_name . " deleted event - <b>" . $event[0]['title'] . "</b> ID <b>#" . $event[0]['id'] . "</b>";

				$notification_data = array(
					'user_id' => $this->session->userdata('user_id'),
					'workspace_id' => $this->session->userdata('workspace_id'),
					'title' => $title,
					'user_ids' => $user_ids,
					'type' => 'event',
					'type_id' => $event_id,
					'notification' => $notification,
				);
				// if ($event[0]['is_public'] == 1 && !empty($user_ids)) {
				// 	$this->notifications_model->store_notification($notification_data);
				// }
				$this->session->set_flashdata('message', 'Event deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = 'Event deleted successfully';
			} elseif ($result == 2) {
				$this->session->set_flashdata('message', 'You are not authorized to delete this event');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'You are not authorized to delete this event';
			} elseif ($result == 0) {
				$this->session->set_flashdata('message', 'Event could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Event could not be deleted! Try again!';
			}

			echo json_encode($response);
		}
		// redirect('events', 'refresh');

	}
}
