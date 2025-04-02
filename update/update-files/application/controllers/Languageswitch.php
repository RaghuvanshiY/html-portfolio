<?php if (!defined('BASEPATH')) exit('Direct access allowed');
class Languageswitch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model(['users_model', 'workspace_model']);
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
        $this->load->library('session');
    }
    function switchlang($language = "")
    {
        $language = urldecode($language);
        if (!$this->ion_auth->logged_in()) {
            redirect('auth', 'refresh');
        } else {
            $language = ($language != "") ? $language : "english";
            $workspace_id = $this->session->userdata('workspace_id');
            $user_id = $this->session->userdata('user_id');

            if ($this->users_model->update_user_lang($workspace_id, $user_id, $language)) {
                $this->session->set_userdata('site_lang', $language);
                $this->session->set_flashdata('message', 'Language changed successfully.');
                $this->session->set_flashdata('message_type', 'success');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $this->session->set_flashdata('message', 'Language not changed.');
                $this->session->set_flashdata('message_type', 'error');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
}
