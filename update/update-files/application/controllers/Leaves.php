<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Leaves extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['users_model', 'leaves_model', 'notes_model', 'notifications_model', 'workspace_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
	}

	public function leave_editors()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}
		if (!empty($this->input->post('users'))) {
			$user_ids = implode(",", $this->input->post('users'));
		} else {
			$user_ids = '';
		}

		$data = array(
			'leave_editors' => $user_ids,
		);
		$id = $this->session->userdata('workspace_id');

		if ($this->leaves_model->leave_editors($data, $id)) {
			$this->session->set_flashdata('message', 'Editors update successfully.');
			$this->session->set_flashdata('message_type', 'success');

			$response['error'] = false;

			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = 'Successful';
			echo json_encode($response);
		} else {
			$this->session->set_flashdata('message', 'Editors could not updated! Try again!');
			$this->session->set_flashdata('message_type', 'error');
			$response['error'] = true;

			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['message'] = validation_errors();
			echo json_encode($response);
		}
	}

	public function get_leave_editor_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$id = $this->session->userdata('workspace_id');
			$data = $this->leaves_model->get_leave_editor_by_id($id);

			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}

	public function get_leave_by_id()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			$leave_id = $this->input->post('id');


			if (empty($leave_id) || !is_numeric($leave_id) || $leave_id < 1) {
				redirect('projects', 'refresh');
				return false;
				exit(0);
			}

			$data = $this->leaves_model->get_leave_by_id($leave_id);

			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();

			echo json_encode($data[0]);
		}
	}

	public function approve()
	{
		if (!$this->ion_auth->logged_in() && (!$this->ion_auth->is_admin() || !is_editor() || !is_leaves_editor())) {
			redirect('auth', 'refresh');
		}

		$id = $this->uri->segment(3);

		if (!empty($id) && is_numeric($id)) {
			$leave = $this->leaves_model->get_leave_by_id($id);
			if (!empty($leave) && isset($leave[0])) {
				$data = array(
					'action_by' => $this->session->userdata('user_id'),
					'status' => 1
				);

				if ($this->leaves_model->approve($id, $data)) {
					$this->session->set_flashdata('message', 'Leave approved successfully.');
					$this->session->set_flashdata('message_type', 'success');
				} else {
					$this->session->set_flashdata('message', 'Leave could not be approved! Try again!');
					$this->session->set_flashdata('message_type', 'error');
				}
			} else {
				$this->session->set_flashdata('message', 'This leave was deleted!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('leaves', 'refresh');
	}

	public function disapprove()
	{
		if (!$this->ion_auth->logged_in() && (!$this->ion_auth->is_admin() || !is_editor() || !is_leaves_editor())) {
			redirect('auth', 'refresh');
		}

		$id = $this->uri->segment(3);

		if (!empty($id) && is_numeric($id)) {
			$leave = $this->leaves_model->get_leave_by_id($id);
			$leave = $this->leaves_model->get_leave_by_id($id);
			if (!empty($leave) && isset($leave[0])) {
				$data = array(
					'action_by' => $this->session->userdata('user_id'),
					'status' => 2
				);

				if ($this->leaves_model->disapprove($id, $data)) {
					$this->session->set_flashdata('message', 'Leave disapproved successfully.');
					$this->session->set_flashdata('message_type', 'success');
				} else {
					$this->session->set_flashdata('message', 'Leave could not be disapproved! Try again!');
					$this->session->set_flashdata('message_type', 'error');
				}
			} else {
				$this->session->set_flashdata('message', 'This leave was deleted!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('leaves', 'refresh');
	}

	public function get_leaves_list($id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = !empty($id && is_numeric($id)) ? $id : (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : $this->session->userdata('user_id'));
			if (!empty($id && is_numeric($id))) {
				$user_detail = 'yes';
			} elseif (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3)))) {
				$user_detail = 'yes';
			} else {
				$user_detail = 'no';
			}
			return $this->leaves_model->get_leaves_list($workspace_id, $user_id, $user_detail);
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

			$product_ids = explode(',', $user->workspace_id);

			$section = array_map('trim', $product_ids);

			$product_ids = $section;

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($product_ids);
			if (!empty($workspace)) {
				if (!$this->session->has_userdata('workspace_id')) {
					$this->session->set_userdata('workspace_id', $workspace[0]->id);
				}
			}

			if ($this->session->has_userdata('workspace_id')) {
				$notes = $this->notes_model->get_note($this->session->userdata('workspace_id'), $this->session->userdata('user_id'));

				if (!empty($notes)) {
					$data['notes'] = $notes;
				}
				$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
				$user_ids = explode(',', $current_workspace_id[0]->user_id);
				$section = array_map('trim', $user_ids);
				$data['all_user'] = $this->users_model->get_user($user_ids);
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$data['is_admin'] =  $this->ion_auth->is_admin();
				$this->load->view('leaves', $data);
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

		$this->form_validation->set_rules('leave_days', str_replace(':', '', 'Leave Days is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('leave_from', str_replace(':', '', 'Leave Date is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('leave_to', str_replace(':', '', 'Leave Date is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('reason', str_replace(':', '', 'Leave reson is empty.'), 'trim|required|xss_clean|strip_tags');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'user_id' => $this->session->userdata('user_id'),
				'workspace_id' => $this->session->userdata('workspace_id'),
				'leave_days' => $this->input->post('leave_days', true),
				'leave_from' => $this->input->post('leave_from', true),
				'leave_to' => $this->input->post('leave_to', true),
				'reason' => $this->input->post('reason', true)
			);
			$note_id = $this->leaves_model->create_leave($data);

			if ($note_id != false) {
				$this->session->set_flashdata('message', 'Leave request submited successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Leave request could not submited! Try again!');
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

		$this->form_validation->set_rules('leave_days', str_replace(':', '', 'Leave Days is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('leave_from', str_replace(':', '', 'Leave Date is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('leave_to', str_replace(':', '', 'Leave Date is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('reason', str_replace(':', '', 'Leave reson is empty.'), 'trim|required|xss_clean|strip_tags');
		$this->form_validation->set_rules('update_id', str_replace(':', '', 'Update id is empty.'), 'trim|required|is_numeric');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'leave_days' => $this->input->post('leave_days', true),
				'leave_from' => $this->input->post('leave_from', true),
				'leave_to' => $this->input->post('leave_to', true),
				'reason' => $this->input->post('reason', true)
			);
			$id = $this->input->post('update_id', true);
			if ($this->leaves_model->edit_leave($data, $id)) {
				$this->session->set_flashdata('message', 'Leave request update successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Leave request could not updated! Try again!');
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

		$id = $this->uri->segment(3);
		if (!empty($id) && is_numeric($id) || $id < 1) {
			if ($this->leaves_model->delete_leave($id)) {
				$this->session->set_flashdata('message', 'Leave request deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Leave request could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
	}

}
