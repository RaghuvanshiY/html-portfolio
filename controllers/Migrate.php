<?php

class Migrate extends CI_Controller
{

    public function index()
    {
        if (!is_admin()) {
            redirect('home', 'refresh');
        }

        $this->load->library('migration');
        $this->migration->current();
        return true;
    }
}
