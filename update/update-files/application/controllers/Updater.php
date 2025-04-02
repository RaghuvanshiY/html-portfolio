<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Updater extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model(['workspace_model', 'notifications_model']);
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->library(['session']);
    }

    public function index()
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

            $data['is_admin'] = $this->ion_auth->is_admin();

            if ($this->db->table_exists('updates')) {
                $data['db_current_version'] = $db_current_version = get_system_version();
            } else {
                $data['db_current_version'] = $db_current_version = 1.0;
            }

            if (file_exists("update/updater.txt")) {
                $lines_array = file("update/updater.txt");
                $search_string = "version";

                foreach ($lines_array as $line) {
                    if (strpos($line, $search_string) !== false) {
                        list(, $new_str) = explode(":", $line);
                        // If you don't want the space before the word bong, uncomment the following line.
                        $new_str = trim($new_str);
                    }
                }
                $data['file_current_version'] = $file_current_version = $new_str;
            } else {
                $data['file_current_version'] = $file_current_version = false;
            }

            if ($file_current_version != false && $file_current_version > $db_current_version) {

                $data['is_updatable'] = $is_updatable = true;
            } else {
                $data['is_updatable'] = $is_updatable = false;
            }
            $workspace_id = $this->session->userdata('workspace_id');
            $data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
            $this->load->view('updater', $data);
        }
    }


    public function update()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth', 'refresh');
        } else {

            if ($this->db->table_exists('updates')) {
                $data['db_current_version'] = $db_current_version = get_system_version();
            } else {
                $data['db_current_version'] = $db_current_version = 1.0;
            }

            if (file_exists("update/updater.txt")) {
                $lines_array = file("update/updater.txt");
                $search_string = "version";

                foreach ($lines_array as $line) {
                    if (strpos($line, $search_string) !== false) {
                        list(, $new_str) = explode(":", $line);
                        // If you don't want the space before the word bong, uncomment the following line.
                        $new_str = trim($new_str);
                    }
                }
                $data['file_current_version'] = $file_current_version = $new_str;
            } else {
                $data['file_current_version'] = $file_current_version = false;
            }

            if ($file_current_version != false && $file_current_version > $db_current_version) {
                $data['is_updatable'] = $is_updatable = true;
            } else {
                $data['is_updatable'] = $is_updatable = false;

                $this->session->set_flashdata('message', 'System not updated successfully.');
                $this->session->set_flashdata('message_type', 'error');

                $response['error'] = true;

                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = 'not Successful';
                echo json_encode($response);

                return false;
            }


            if (file_exists("update/filepathsdir.json")) {
                $lines_array = file_get_contents("update/filepathsdir.json");
                $lines_array = json_decode($lines_array);
                foreach ($lines_array as $key => $line) {
                    if (!is_dir($line) && !file_exists($line)) {
                        mkdir($line, 0777, true);
                    }
                }
            }

            if (file_exists("update/filepaths.json")) {
                $lines_array = file_get_contents("update/filepaths.json");
                $lines_array = json_decode($lines_array);
                foreach ($lines_array as $key => $line) {
                    copy($key, $line);
                }
            }

            $this->load->library('migration');
            $this->migration->current();

            $data = array('version' => $file_current_version);
            $this->db->insert('updates', $data);

            delete_files("update", true);

            $this->session->set_flashdata('message', 'System updated successfully.');
            $this->session->set_flashdata('message_type', 'success');

            $response['error'] = false;

            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = 'Successful';
            echo json_encode($response);
        }
    }

    public function upload()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth', 'refresh');
        }
        if (!empty($_FILES['file']['name'])) {
            $file_names = array();
            if (is_dir('./update/')) {
                delete_files("update", true);
            } else {
                mkdir('./update/', 0777, TRUE);
            }
            $config = array();
            $config['upload_path'] = './update/';
            $config['allowed_types'] = 'zip';
            $config['max_size']      = '0';
            $config['max_height']      = '0';
            $config['max_width']      = '0';
            $config['overwrite']     = FALSE;
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('file')) {
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];
                $zip = new ZipArchive;
                $res = $zip->open("./update/" . $filename);
                if ($res === TRUE) {

                    // Unzip path
                    $extractpath = "./update";

                    // Extract file
                    $zip->extractTo($extractpath);
                    $zip->close();
                    /* check if zip is valid or not */
                    $sub_directory = (file_exists("update/update/updater.txt")) ? "update/" : "";
                    if (!is_dir('update/' . $sub_directory . 'update-files') || !file_exists("update/" . $sub_directory . "updater.txt")) {
                        delete_files("update", true);
                        $response['error'] = true;
                        $this->session->set_flashdata('message', 'It seems you have uploaded wrong zip file.');
                        $this->session->set_flashdata('message_type', 'error');
                        return false;
                        exit();
                    }
                    /* if valid check for the version of the uploaded files */
                    if (file_exists("update/" . $sub_directory . "updater.txt")) {
                        $lines_array = file("update/" . $sub_directory . "updater.txt");
                        $search_string = "version";

                        foreach ($lines_array as $line) {
                            if (strpos($line, $search_string) !== false) {
                                list(, $new_str) = explode(":", $line);
                                // If you don't want the space before the word bong, uncomment the following line.
                                $new_str = trim($new_str);
                            }
                        }
                        $file_current_version = $new_str;
                    } else {
                        $file_current_version = false;
                    }
                    $db_current_version = get_system_version();

                    if ($file_current_version < $db_current_version) {
                        delete_files("update", true);
                        $response['error'] = true;
                        $this->session->set_flashdata('message', 'It seems you are not following update sequence.');
                        $this->session->set_flashdata('message_type', 'error');
                        return false;
                        exit();
                    } else {
                        /* if everything is okay update the system */
                        if ($file_current_version != false && $file_current_version > $db_current_version) {
                            $data['is_updatable'] = $is_updatable = true;
                        } else {
                            $data['is_updatable'] = $is_updatable = false;

                            $this->session->set_flashdata('message', 'System could not be updated!');
                            $this->session->set_flashdata('message_type', 'error');

                            $response['error'] = true;

                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            $response['message'] = 'System could not be updated!';
                            echo json_encode($response);
                            return false;
                        }

                        /* create folders, copy files and run migration */
                        if (file_exists("update/" . $sub_directory . "filepathsdir.json")) {
                            $lines_array = file_get_contents("update/" . $sub_directory . "filepathsdir.json");
                            $lines_array = json_decode($lines_array);
                            foreach ($lines_array as $key => $line) {
                                if (!is_dir($line) && !file_exists($line)) {
                                    mkdir($line, 0777, true);
                                }
                            }
                        }
                        /* copy files */
                        if (file_exists("update/" . $sub_directory . "filepaths.json")) {
                            $lines_array = file_get_contents("update/" . $sub_directory . "filepaths.json");
                            $lines_array = json_decode($lines_array);
                            foreach ($lines_array as $key => $line) {
                                copy($key, $line);
                            }
                        }
                        /* run migration */
                        $this->load->library('migration');
                        $this->migration->current();
                        /* update version in database */
                        $data = array('version' => $file_current_version);
                        $this->db->insert('updates', $data);

                        delete_files("update", true);

                        $response['error'] = false;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response['message'] = 'Successful';
                        $this->session->set_flashdata('message', 'System updated successfully.');
                        $this->session->set_flashdata('message_type', 'success');
                        echo json_encode($response);
                        return false;
                        exit();
                    }
                } else {
                    $response['error'] = true;
                    $this->session->set_flashdata('message', 'File not uploaded.');
                    $this->session->set_flashdata('message_type', 'error');
                }
            }
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($response);
        }
    }
}
