<?php
/* <!--  START 
===============================================
Version :- V.2 		Author : ''    23-July-2020
=============================================== 
-->
*/
defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'expense_model', 'notifications_model', 'users_model']);
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
			}

			$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));
			$expense_types = $this->expense_model->get_expense_types($this->session->userdata('workspace_id'));
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;
			$data['all_user'] = $this->users_model->get_user($user_ids);
			$data['expense_types'] = $expense_types;
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('expenses', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}
	public function get_expense_types()
	{
		// return false;
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
			$workspace_id = $this->session->userdata('workspace_id');
			$data =  $this->expense_model->get_expense_types($workspace_id);
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['data'] = $data;
			print_r(json_encode($response));
			//  $data;

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

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('expense_type_id', str_replace(':', '', 'Expense Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('user_id', str_replace(':', '', 'User is empty.'), 'trim|required');
		$this->form_validation->set_rules('amount', str_replace(':', '', 'Amount is empty.'), 'trim|required');
		$this->form_validation->set_rules('expense_date', str_replace(':', '', 'Expense Date is empty.'), 'trim|required');
		if ($this->form_validation->run() === TRUE) {
			$expense_date = strip_tags($this->input->post('expense_date', true));
			$data = array(
				'workspace_id' => $this->session->userdata('workspace_id'),
				'user_id' => strip_tags($this->input->post('user_id', true)),
				'expense_type_id' => strip_tags($this->input->post('expense_type_id', true)),
				'title' => strip_tags($this->input->post('title', true)),
				'note' => strip_tags($this->input->post('note', true)),
				'amount' => strip_tags($this->input->post('amount', true)),
				'expense_date' => !empty($expense_date) ? $expense_date : ""

			);
			$expense_id = $this->expense_model->add_expense($data);

			if ($expense_id != false) {
				$this->session->set_flashdata('message', 'Expense Added Successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Expense could not Created! Try again!');
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
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}

		$this->form_validation->set_rules('update_title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_expense_type_id', str_replace(':', '', 'Expense Type is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_user_id', str_replace(':', '', 'User is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_amount', str_replace(':', '', 'Amount is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_expense_date', str_replace(':', '', 'Expense Date is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$expense_date = strip_tags($this->input->post('update_expense_date', true));
			$data = array(
				'user_id' => strip_tags($this->input->post('update_user_id', true)),
				'expense_type_id' => strip_tags($this->input->post('update_expense_type_id', true)),
				'title' => strip_tags($this->input->post('update_title', true)),
				'note' => strip_tags($this->input->post('update_note', true)),
				'amount' => strip_tags($this->input->post('update_amount', true)),
				'expense_date' => !empty($expense_date) ? $expense_date : ""

			);
			$id = strip_tags($this->input->post('update_id', true));
			if ($this->expense_model->edit_expense($data, $id)) {
				$this->session->set_flashdata('message', 'Expense Added Successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Expense could not Created! Try again!');
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
		if (!is_admin()) {
			$this->session->set_flashdata('message', 'You are not authorized to access this page!');
			$this->session->set_flashdata('message_type', 'error');
			redirect('home', 'refresh');
			return false;
			exit();
		}

		$id = $this->uri->segment(3);
		if (!empty($id) && is_numeric($id) && $id != 'undefined') {
			if ($this->expense_model->delete_expense($id)) {
				$this->session->set_flashdata('message', 'Expense deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
				$response['error'] = false;
			} else {
				$this->session->set_flashdata('message', 'Expense could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Expense could not be deleted! Try again!';
			}
		} else {
			$this->session->set_flashdata('message', 'Expense could not be deleted! Try again!');
			$this->session->set_flashdata('message_type', 'error');

			$response['error'] = true;
			$response['message'] = 'Expense could not be deleted! Try again!';
		}
		echo json_encode($response);
	}

	public function expense_types()
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
			}
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('expense-types.php', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}
	public function create_expense_type()
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

		$this->form_validation->set_rules('title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('description', str_replace(':', '', 'description is empty.'), 'trim|required');
		if ($this->form_validation->run() === TRUE) {

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'description' => strip_tags($this->input->post('description', true)),
				'workspace_id' => $this->session->userdata('workspace_id')
			);
			$expense_type_id = $this->expense_model->add_expense_type($data);

			if ($expense_type_id != false) {
				$response['error'] = false;
				$response['message'] = 'Expense Type Added successfully.';
				$response['expense_type_id'] = $expense_type_id;
				$this->session->set_flashdata('message', 'Expense Type Added Successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Expense Type could not Added! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Expense Type could not Added! Try again!';
			}
		} else {
			$response['error'] = true;
			$response['message'] = validation_errors();
		}
		$response['csrfName'] = $this->security->get_csrf_token_name();
		$response['csrfHash'] = $this->security->get_csrf_hash();
		echo json_encode($response);
	}

	public function get_expense_list()
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
			$workspace_id = $this->session->userdata('workspace_id');
			return $this->expense_model->get_expense_list($workspace_id);
		}
	}

	public function get_expense_by_id()
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

			$id = $this->input->post('id');

			if (empty($id) || !is_numeric($id)) {
				redirect('expenses', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->expense_model->get_expense_by_id($id);
			$data[0]['expense_date'] = date('Y-m-d\TH:i', strtotime($data[0]['expense_date']));
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
		}
	}
	public function get_expense_type_by_id()
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

			$id = $this->input->post('id');

			if (empty($id) || !is_numeric($id)) {
				redirect('expenses/expense_types', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->expense_model->get_expense_type_by_id($id);
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
		}
	}

	public function edit_expense_type()
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

		$this->form_validation->set_rules('update_title', str_replace(':', '', 'Title is empty.'), 'trim|required');
		$this->form_validation->set_rules('update_description', str_replace(':', '', 'description is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('update_title', true)),
				'description' => strip_tags($this->input->post('update_description', true))
			);

			if ($this->expense_model->edit_expense_type($data, $this->input->post('update_id'))) {
				$this->session->set_flashdata('message', 'Expense Type Updated Successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Expense Type could not Updated! Try again!');
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

	public function get_expense_type_list()
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
			$workspace_id = $this->session->userdata('workspace_id');
			return $this->expense_model->get_expense_type_list($workspace_id);
		}
	}


	public function delete_expense_type()
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

		$id = $this->uri->segment(3);
		if (!empty($id) && is_numeric($id)  || $id < 1) {
			if ($this->expense_model->delete_expense_type($id)) {

				$this->session->set_flashdata('message', 'Expense Type Deleted Successfully.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
			} else {

				$this->session->set_flashdata('message', 'Expense Type could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Expense type could not be deleted! Try again!';
			}
		}
		echo json_encode($response);
	}
}
/* 
// END
// ===============================================
// Version : V.2 		Author : ''    23-July-2020
// =============================================== 
*/
