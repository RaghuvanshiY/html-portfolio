<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Languages extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'settings_model', 'chat_model', 'language_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language', 'file']);
		$this->load->library('session');
	}

	public function index()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			if (!is_admin()) {
				redirect('home', 'refresh');
			}

			$my_lang = $this->session->userdata('site_lang');
			redirect('languages/change/' . $my_lang, 'refresh');
		}
	}

	public function change($lang = '')
	{

		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			if (!is_admin()) {
				redirect('home', 'refresh');
			}
			if (!empty($lang) && $lang != 'index') {
				if (file_exists('./application/language/' . $lang . '/taskhub_labels_lang.php')) {
					$this->lang->load('taskhub_labels_lang', $lang);
					$data['active_tab_lang'] = $lang;
				} else {
					$my_lang = $this->session->userdata('site_lang');
					redirect('languages/change/' . $my_lang, 'refresh');
				}
			} else {
				$my_lang = $this->session->userdata('site_lang');
				redirect('languages/change/' . $my_lang, 'refresh');
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
			// if (!empty($workspace_id)) {
			$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
			$data['is_admin'] =  $this->ion_auth->is_admin();
			$this->load->view('languages', $data);
			// } else {
			// 	redirect('home', 'refresh');
			// }
		}
	}

	public function save_languages()
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
			redirect('auth', 'refresh');
		} else {

			$my_lang = strip_tags($this->input->post('language', true));
			$lang = array();
			$langstr = '';
			$data = $this->input->post();
			foreach ($data as $key => $value) {
				$label_data =  strip_tags($value);
				$label_data = $this->db->escape_str($label_data);
				$label_key = $key;
				$langstr .= "\$lang['label_" . $label_key . "'] = \"$label_data\";" . "\n";
			}

			$langstr_final = "<?php defined('BASEPATH') OR exit('No direct script access allowed');
			/**
			*
			*
			* Description:  " . $my_lang . " language file for general labels
			*
			*/" . "\n\n\n" . $langstr;

			if (file_exists('./application/language/' . $my_lang . '/taskhub_labels_lang.php')) {
				delete_files('./application/language/' . $my_lang . '/taskhub_labels_lang.php');
			}

			if ($this->input->post('is_rtl') && $this->input->post('is_rtl') == 'on') {
				$is_rtl = 1;
			} else {
				$is_rtl = 0;
			}

			$data = array(
				'is_rtl' => $is_rtl
			);

			if (write_file('./application/language/' . $my_lang . '/taskhub_labels_lang.php', $langstr_final)) {
				$this->language_model->update_language($my_lang, $data);

				$this->session->set_flashdata('message', 'Language Updated successfully.');
				$this->session->set_flashdata('message_type', 'success');

				$response['error'] = false;
				$response['message'] = 'Successful';
				echo json_encode($response);
			} else {
				$this->session->set_flashdata('message', 'Language could not Updated! Try again!');
				$this->session->set_flashdata('message_type', 'error');

				$response['error'] = true;
				$response['message'] = 'Not Successful';
				echo json_encode($response);
			}
		}
	}

	public function create()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('language', str_replace(':', '', 'language is empty.'), 'trim|required|strtolower');
		$this->form_validation->set_rules('code', str_replace(':', '', 'code is empty.'), 'trim|required|strtolower');

		if ($this->form_validation->run() === TRUE) {

			$my_lang =  strip_tags($this->input->post('language', true));

			if (!preg_match('/^[a-z_-]+$/i', $my_lang)) {
				$this->session->set_flashdata('message', 'Language name should be in English letters.');
				$this->session->set_flashdata('message_type', 'error');
				$response['error'] = true;
				$response['message'] = 'Language name should be in English letters.';
				echo json_encode($response);
				return false;
			}

			if ($this->input->post('is_rtl') && $this->input->post('is_rtl') == 'on') {
				$is_rtl = 1;
			} else {
				$is_rtl = 0;
			}

			$data = array(
				'language' => $my_lang,
				'code' => strip_tags($this->input->post('code', true)),
				'is_rtl' => $is_rtl
			);

			$id = $this->language_model->create_language($data);

			if (!empty($id)) {

				$langstr = "\$lang['label_language'] = \"$my_lang\";" . "\n";

				$langstr_final = "<?php defined('BASEPATH') OR exit('No direct script access allowed');
				/**
				*
				*
				* Description:  " . $my_lang . " language file for general labels
				*
				*/" . "\n\n\n" . $langstr;

				if (!is_dir('./application/language/' . $my_lang . '/')) {
					mkdir('./application/language/' . $my_lang . '/', 0777, TRUE);
				}

				if (file_exists('./application/language/' . $my_lang . '/taskhub_labels_lang.php')) {
					delete_files('./application/language/' . $my_lang . '/taskhub_labels_lang.php');
					write_file('./application/language/' . $my_lang . '/taskhub_labels_lang.php', $langstr_final);
				} else {
					write_file('./application/language/' . $my_lang . '/taskhub_labels_lang.php', $langstr_final);
				}

				$this->session->set_flashdata('message', 'Language Created successfully.');
				$this->session->set_flashdata('message_type', 'success');
			} else {
				$this->session->set_flashdata('message', 'Language could not Created! Try again!');
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

	public function setting_detail()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {

			if (!is_admin()) {
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
			$data['is_admin'] =  $this->ion_auth->is_admin();
			$this->load->view('setting-detail', $data);
		}
	}
}
