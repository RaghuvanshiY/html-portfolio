<?php
/* <!--  START 
===============================================
Version :- V.2 		Author : ''    23-July-2020
=============================================== 
-->
*/
defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'payments_model', 'notifications_model', 'users_model', 'invoices_model']);
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
			$user_ids = explode(',', $current_workspace_id[0]->user_id);
			$section = array_map('trim', $user_ids);
			$user_ids = $section;
			$data['all_user'] = $this->users_model->get_user($user_ids, ['1', '2', '3']);
			$workspace_id = $this->session->userdata('workspace_id');
			if (!empty($workspace_id)) {
				$data['invoices'] = $this->invoices_model->get_invoices($workspace_id);
				$data['payment_modes'] = $this->payments_model->get_payment_modes($workspace_id);
				$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
				$this->load->view('payments', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function create_payment()
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

		$this->form_validation->set_rules('payment_mode_id', str_replace(':', '', 'Payment mode is empty.'), 'trim|required');
		$this->form_validation->set_rules('amount', str_replace(':', '', 'Amount is empty.'), 'trim|required');
		$this->form_validation->set_rules('payment_date', str_replace(':', '', 'Payment date is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$amount = $this->input->post('amount');
			$invoice_id = $this->input->post('invoice_id');
			if (!empty($invoice_id)) {
				$amount_left = $this->payments_model->get_amount_left($invoice_id);
				if ($amount > $amount_left) {
					$response['error'] = true;
					$response['csrfName'] = $this->security->get_csrf_token_name();
					$response['csrfHash'] = $this->security->get_csrf_hash();
					$response['message'] = 'Amount should not be greater than remaining amount - ' . $amount_left;
					echo json_encode($response);
					return false;
					exit();
				}
			}

			$data = array(
				'workspace_id' => $this->session->userdata('workspace_id'),
				'invoice_id' => strip_tags($this->input->post('invoice_id', true)),
				'user_id' => strip_tags($this->input->post('user_id', true)),
				'note' => strip_tags($this->input->post('note', true)),
				'payment_mode_id' => strip_tags($this->input->post('payment_mode_id', true)),
				'amount' => strip_tags($this->input->post('amount', true)),
				'payment_date' => strip_tags($this->input->post('payment_date', true))

			);
			$payment_id = $this->payments_model->add_payment($data);

			if ($payment_id != false) {
				$this->session->set_flashdata('message', 'Payment Added successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment could not Created! Try again!');
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

	public function get_payments_list()
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
			return $this->payments_model->get_payments_list($workspace_id);
		}
	}
	public function get_payment_by_id()
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
				redirect('payments', 'refresh');
				return false;
				exit(0);
			}
			$data = $this->payments_model->get_payment_by_id($id);
			$data[0]['payment_date'] = date('Y-m-d\TH:i', strtotime($data[0]['payment_date']));
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
		}
	}
	public function payment_modes()
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
				$this->load->view('payment-modes', $data);
			} else {
				redirect('home', 'refresh');
			}
		}
	}

	public function get_payment_mode_list()
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
			return $this->payments_model->get_payment_mode_list($workspace_id);
		}
	}

	public function create_payment_mode()
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
		if ($this->form_validation->run() === TRUE) {

			$data = array(
				'title' => strip_tags($this->input->post('title', true)),
				'workspace_id' => $this->session->userdata('workspace_id')
			);
			$payment_mode_id = $this->payments_model->add_payment_mode($data);

			if ($payment_mode_id != false) {
				$response['error'] = false;
				$response['message'] = 'Payment Mode Added successfully.';
				$response['payment_mode_id'] = $payment_mode_id;
				$this->session->set_flashdata('message', 'Payment Mode Added Successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment Mode could not Added! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Payment Mode could not Added! Try again!';
			}
		} else {
			$response['error'] = true;
			$response['message'] = validation_errors();
		}
		$response['csrfName'] = $this->security->get_csrf_token_name();
		$response['csrfHash'] = $this->security->get_csrf_hash();
		echo json_encode($response);
	}

	public function get_payment_mode_by_id($id = '')
	{
		if ($this->ion_auth->logged_in() && !empty($id) && is_numeric($id)) {
			if (!is_admin()) {
				$this->session->set_flashdata('message', 'You are not authorized to access this page!');
				$this->session->set_flashdata('message_type', 'error');
				redirect('home', 'refresh');
				return false;
				exit();
			}
			$data = $this->payments_model->get_payment_mode_by_id($id);
			if (!empty($data)) {
				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($data[0]);
			} else {
				$data[0]['csrfName'] = $this->security->get_csrf_token_name();
				$data[0]['csrfHash'] = $this->security->get_csrf_hash();
				echo json_encode($data[0]);
			}
		} else {
			$data[0]['csrfName'] = $this->security->get_csrf_token_name();
			$data[0]['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data[0]);
		}
	}

	public function edit_payment()
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
		$this->form_validation->set_rules('payment_mode_id', str_replace(':', '', 'Payment mode is empty.'), 'trim|required');
		$this->form_validation->set_rules('amount', str_replace(':', '', 'Amount is empty.'), 'trim|required');
		$this->form_validation->set_rules('payment_date', str_replace(':', '', 'Payment date is empty.'), 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'invoice_id' => strip_tags($this->input->post('invoice_id', true)),
				'user_id' => strip_tags($this->input->post('user_id', true)),
				'note' => strip_tags($this->input->post('note', true)),
				'payment_mode_id' => strip_tags($this->input->post('payment_mode_id', true)),
				'amount' => strip_tags($this->input->post('amount', true)),
				'payment_date' => strip_tags($this->input->post('payment_date', true))
			);

			if ($this->payments_model->edit_payment($data, $this->input->post('id'))) {
				$this->session->set_flashdata('message', 'Paymnet Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment could not Updated! Try again!');
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

	public function edit_payment_mode()
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

		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'title' => strip_tags($this->input->post('title', true))
			);

			if ($this->payments_model->edit_payment_mode($data, $this->input->post('update_id'))) {
				$this->session->set_flashdata('message', 'Paymnet Mode Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment Mode could not Updated! Try again!');
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
	public function get_payment_modes()
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
			$data =  $this->payments_model->get_payment_modes($workspace_id);
			$response['csrfName'] = $this->security->get_csrf_token_name();
			$response['csrfHash'] = $this->security->get_csrf_hash();
			$response['data'] = $data;
			print_r(json_encode($response));
			//  $data;

		}
	}
	public function delete_payment_mode()
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
		$payment_mode_id = $this->uri->segment(3);
		if (!empty($payment_mode_id) && is_numeric($payment_mode_id)  || $payment_mode_id < 1) {
			if ($this->payments_model->delete_payment_mode($payment_mode_id)) {
				$this->session->set_flashdata('message', 'Payment Mode deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment Mode could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('payments/payment_modes', 'refresh');
	}
	public function delete_payment()
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
		$payment_id = $this->uri->segment(3);
		if (!empty($payment_id) && is_numeric($payment_id)  || $payment_id < 1) {
			if ($this->payments_model->delete_payment($payment_id)) {
				$this->session->set_flashdata('message', 'Payment deleted successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Payment could not be deleted! Try again!');
				$this->session->set_flashdata('message_type', 'error');
			}
		}
		redirect('payments', 'refresh');
	}
}
/* 
// END
// ===============================================
// Version : V.2 		Author : ''    23-July-2020
// =============================================== 
*/
