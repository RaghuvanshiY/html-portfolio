<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leaves_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
    }

    function leave_editors($data, $id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('workspace', $data))
            return true;
        else
            return false;
    }

    function get_leave_editor_by_id($id)
    {
        $query = $this->db->query('SELECT * FROM workspace WHERE id=' . $id . ' ');
        return $query->result_array();
    }

    function get_leave_by_id($id)
    {
        $query = $this->db->query('SELECT * FROM leaves WHERE id=' . $id . ' ');
        return $query->result_array();
    }

    function approve($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('status', 0);
        $this->db->where('status != ', 2);
        if ($this->db->update('leaves', $data))
            return true;
        else
            return false;
    }

    function disapprove($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('status', 0);
        $this->db->where('status != ', 1);
        if ($this->db->update('leaves', $data))
            return true;
        else
            return false;
    }

    function get_leaves_list($workspace_id, $user_id, $user_detail = 'no')
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
            $where = " and (u.first_name like '%" . $search . "%' OR u.last_name like '%" . $search . "%' OR l.leave_days like '%" . $search . "%' OR l.status like '%" . $search . "%' OR l.leave_from like '%" . $search . "%' OR l.leave_to like '%" . $search . "%' OR l.reason like '%" . $search . "%')";
        }

        if ($user_detail == 'no') {
            if (!$this->ion_auth->is_admin($user_id) && !is_editor($user_id) && !is_leaves_editor($user_id)) {
                $where .= " AND l.user_id=$user_id";
            }
        } else {
            $where .= " AND l.user_id=$user_id";
        }
        $left_join = "LEFT JOIN users u ON u.id=l.user_id LEFT JOIN users au ON au.id=l.action_by";

        $query = $this->db->query("SELECT COUNT(l.id) as total FROM leaves l $left_join WHERE l.workspace_id=$workspace_id " . $where);

        $res = $query->result_array();
        foreach ($res as $row) {
            $total = $row['total'];
        }

        $query = $this->db->query("SELECT l.*,u.first_name,u.last_name,au.first_name as au_first_name,au.last_name as au_last_name FROM leaves l $left_join WHERE l.workspace_id=$workspace_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);

        // print_r($this->db->last_query());    
        $res = $query->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($res as $row) {
            $action = '';
            $profile = '';
            $tempRow['id'] = $row['id'];
            $tempRow['first_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $leave_from = date_create($row['leave_from']);
            $leave_to = date_create($row['leave_to']);

            $tempRow['leave_days'] = '<div class="article-category"><span class="text-primary">From:</span>' . date_format($leave_from, "d M Y") . '</div>
                                <div class="article-category"><span class="text-primary">To:</span>' . date_format($leave_to, "d M Y") . '</div>
                                <div class="article-category">
                                <div class="badge badge-info projects-badge">' . $row['leave_days'] . ' Days</div>
                                </div>';
            $tempRow['leave_from'] = $row['leave_from'];
            $tempRow['leave_to'] = $row['leave_to'];
            $tempRow['reason'] = $row['reason'];

            if ($row['status'] == 1) {
                $status = '<div class="badge badge-success projects-badge">' . (!empty($this->lang->line('label_approved')) ? $this->lang->line('label_approved') : 'Approved') . '</div>';
                $action .= $user_id == $row['user_id'] ? '<a href="#" class="btn btn-light mr-2 no-edit-leaves-alert"><i class="fas fa-pen"></i></a>' : '';
            } elseif ($row['status'] == 2) {
                $status = '<div class="badge badge-danger projects-badge">' . (!empty($this->lang->line('label_disapproved')) ? $this->lang->line('label_disapproved') : 'Disapproved') . '</div>';
                $action .= $user_id == $row['user_id'] ? '<a href="#" class="btn btn-light mr-2 no-edit-leaves-alert"><i class="fas fa-pen"></i></a>' : '';
            } else {
                $status = '<div class="badge badge-warning projects-badge">' . (!empty($this->lang->line('label_under_review')) ? $this->lang->line('label_under_review') : 'Under Review') . '</div>';

                $action .= $user_id == $row['user_id'] ? '<a href="#" data-id="' . $row['id'] . '" class="btn btn-light mr-2 modal-edit-leaves-ajax"><i class="fas fa-pen"></i></a>' : '';
            }

            $tempRow['status'] = $status;
            $tempRow['action_by'] = $row['au_first_name'] . ' ' . $row['au_last_name'];

            if ($user_id != $row['user_id']) {
                if ($this->ion_auth->is_admin($user_id) || is_editor($user_id) || is_leaves_editor($user_id)) {
                    if ($row['status'] == 0) {
                        $action .= '<div class="dropdown card-widgets">
                                                <a href="#" class="btn btn-light" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item has-icon leave-action-alert" href="' . base_url('leaves/approve/' . $row['id']) . '" data-id="' . $row['user_id'] . '"><i class="fas fa-check"></i>' . (!empty($this->lang->line('label_approve')) ? $this->lang->line('label_approve') : 'Approve') . '</a>
                                                    <a class="dropdown-item has-icon leave-action-alert" href="' . base_url('leaves/disapprove/' . $row['id']) . '" data-id="' . $row['user_id'] . '"><i class="fas fa-times"></i>' . (!empty($this->lang->line('label_disapprove')) ? $this->lang->line('label_disapprove') : 'Disapprove') . '</a>
                                                    <a class="dropdown-item has-icon delete-leave-request-alert" href="#" data-id="' . $row['id'] . '"><i class="fas fa-trash"></i>' . (!empty($this->lang->line('label_delete')) ? $this->lang->line('label_delete') : 'Delete') . '</a>
                                                </div>
                                            </div>';
                    } else {
                        $action .= '<a class="dropdown-item has-icon delete-leave-request-alert" href="#" data-id="' . $row['id'] . '"><i class="fas fa-trash"></i></a>
                                            ';
                    }
                }
            }

            $action_btns = '<div class="btn-group no-shadow">
                        ' . $action . '
                        </div>';

            $tempRow['action'] = $action_btns;

            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function create_leave($data)
    {
        if ($this->db->insert('leaves', $data))
            return $this->db->insert_id();
        else
            return false;
    }

    function edit_leave($data, $id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('leaves', $data))
            return true;
        else
            return false;
    }

    function delete_leave($id)
    {
        if ($this->db->delete('leaves', array('id' => $id)))
            return true;
        else
            return false;
    }
}
