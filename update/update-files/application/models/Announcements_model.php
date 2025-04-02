<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Announcements_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
    }

    function create_announcement($data)
    {
        $data = escape_array($data);
        if ($this->db->insert('announcements', $data))
            return $this->db->insert_id();
        else
            return false;
    }

    function get_announcements_list($workspace_id)
    {

        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $where = '';
        $get = $this->input->get();
        if (isset($get['sort']))
            $sort = strip_tags($get['sort']);
        if (isset($get['offset']))
            $offset = strip_tags($get['offset']);
        if (isset($get['limit']))
            $limit = strip_tags($get['limit']);
        if (isset($get['order']))
            $order = strip_tags($get['order']);
        if (isset($get['search']) &&  !empty($get['search'])) {
            $search = strip_tags($get['search']);
            $where .= " where (a.id like '%" . $search . "%' OR title LIKE '%" . $search . "%' OR description LIKE '%" . $search . "%' OR username LIKE '%" . $search . "%'  OR date_created LIKE '%" . $search . "%')";
        }
        if (isset($get['type']) && !empty($get['type']) && $get['type'] == 'pinned') {

            $where .= empty($where) ? " where pinned=1" : " and pinned=1";
        }
        if (isset($get['type']) && !empty($get['type']) && $get['type'] == 'unpinned') {
            $where .= empty($where) ? " where pinned=0" : " and pinned=0";
        }
        $where .= empty($where) ? " where a.workspace_id=" . $workspace_id : " and a.workspace_id=" . $workspace_id;

        $query = $this->db->query("SELECT COUNT('id') as total FROM `announcements` a LEFT JOIN users u ON u.id=a.user_id" . $where);

        $res = $query->result_array();
        foreach ($res as $row) {
            $total = $row['total'];
        }

        $query = $this->db->query("SELECT a.*,CONCAT(first_name,' ',last_name) as user_name  FROM announcements a LEFT JOIN users u ON a.user_id=u.id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);

        // print_r($this->db->last_query());
        $res = $query->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        // $this->config->load('taskhub');

        foreach ($res as $row) {

            $temp = $row['pinned'] == 1 ? 'class="make-announcement-unpinned-alert" title="Un-Pin"' : 'class="make-announcement-pinned-alert" title="Pin"';
            $action = is_admin() ? '| <a href="#" ' . $temp . ' data-announcement-id="' . $row['id'] . '"><i class="fas fa-thumbtack text-danger"></i></a> | 
                <a class="delete-announcement-alert" href="#" title="delete" data-announcement-id="' . $row['id'] . '"><i class="fas fa-trash text-danger"></i></a> | <a class="modal-edit-announcement-ajax" href="#" title="edit" data-id="' . $row['id'] . '"><i class="fas fa-edit text-danger"></i></a></div>' : '';
            $title = '<a href="' . base_url('announcements/details/' . $row['id']) . '">' . $row['title'] . '</a>';
            $pinned = $row['pinned'] == 1 ? '<div class="badges">
                <span class="badge badge-info projects-badge">Pinned</span>
              </div>' : '<div class="badges">
              <span class="badge badge-info projects-badge">Un-pinned</span>
            </div>';
            $user = $row['user_id'] == $this->session->userdata('user_id') ? 'You' : $row['user_name'];
            $tempRow['id'] = $row['id'];
            $tempRow['workspace_id'] = $row['workspace_id'];
            $tempRow['user_id'] = $row['user_id'];
            $tempRow['announcement'] = '<div class="ticket-content">
                        <div class="ticket-header">
                          <div class="ticket-detail">
                            <div class="ticket-title">
                              <h4>' . $title . '</h4>
                            </div>
                            ' . $pinned . '
                          </div>
                        </div>
                        
                      </div>
                      <div class="float-right"><div class="font-weight-600 text-muted text-small">' . $user . ' | ' . date("d-M-Y H:i:s", strtotime($row['date_created'])) . $action;
            $tempRow['user_name'] = $user;
            $tempRow['title'] = $title;
            $tempRow['description'] = $row['description'];
            $tempRow['date_created'] = date("d-M-Y H:i:s", strtotime($row['date_created']));


            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function delete_announcement($id)
    {
        $announcement = $this->get_announcement_by_id($id);
        if (!empty($announcement)) {
            if ($this->db->delete('announcements', array('id' => $id)))
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    function get_announcement_by_id($announcement_id)
    {
        $this->db->select('*');
        $this->db->where('id', $announcement_id);
        $query = $this->db->get('announcements');
        return $query->result_array();
    }

    function edit_announcement($data, $id)
    {
        $data = escape_array($data);
        $announcement = $this->get_announcement_by_id($id);
        if (!empty($announcement)) {
            $this->db->where('id', $id);
            if ($this->db->update('announcements', $data))
                return 1;
            else
                return 0;
        }
    }

    function make_announcement_pinned($id)
    {
        $announcement = $this->get_announcement_by_id($id);
        if (!empty($announcement)) {

            $ar = array(
                'pinned' => 1

            );
            $this->db->where('id', $id);
            if ($this->db->update('announcements', $ar))
                return true;
            else
                return false;
        }
    }

    function make_announcement_unpinned($id)
    {
        $announcement = $this->get_announcement_by_id($id);
        if (!empty($announcement)) {

            $ar = array(
                'pinned' => 0

            );
            $this->db->where('id', $id);
            if ($this->db->update('announcements', $ar))
                return true;
            else
                return false;
        }
    }
}
