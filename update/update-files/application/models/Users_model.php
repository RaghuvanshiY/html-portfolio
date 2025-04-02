<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model(['projects_model']);
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
    }

    function get_users_list($workspace_id, $user_id = '')
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
            $where = " and (u.first_name like '%" . $search . "%' OR u.last_name like '%" . $search . "%' OR u.email like '%" . $search . "%' OR u.workspace_id like '%" . $search . "%')";
        }
        if (isset($get['workspace_id']) &&  !empty($get['workspace_id'])) {
            $workspace_id = strip_tags($get['workspace_id']);
        }
        if (isset($get['user_type']) && !empty($get['user_type'])) {
            $where .= " AND ug.group_id =" . $get['user_type'];
        } else {
            $where .= " AND ug.group_id !=3";
        }
        $left_join = 'LEFT JOIN users u ON ug.user_id = u.id';

        $query = $this->db->query("SELECT COUNT(ug.id) as total FROM users_groups ug $left_join WHERE FIND_IN_SET($workspace_id,u.workspace_id) " . $where);
        // print_r($this->db->last_query());
        $res = $query->result_array();
        foreach ($res as $row) {
            $total = $row['total'];
        }

        $query = $this->db->query("SELECT u.* FROM users_groups ug $left_join WHERE FIND_IN_SET($workspace_id,u.workspace_id) " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);

        $res = $query->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $action = '';

        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            if (is_admin()) {
                if (is_client($row['id'])) {
                    $profile = is_admin($row['id']) ? '' : ' <a href="' . base_url('clients/edit-profile/' . $row['id']) . '" target="_blank"><i class="fas fa-edit"></i></a>';
                } else {
                    $profile = is_admin($row['id']) ? '' : ' <a href="' . base_url('users/edit-profile/' . $row['id']) . '" target="_blank"><i class="fas fa-edit"></i></a>';
                }
            } else {
                $profile = '';
            }

            if (!empty($row['profile'])) {
                $first_name = '<li class="media">
                        
                        <a href="' . base_url('assets/profiles/') . '' . $row['profile'] . '" data-lightbox="images" data-title="' . $row['first_name'] . ' ' . $row['last_name'] . '">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="' . base_url('assets/profiles/') . '' . $row['profile'] . '">
                        </a>
                        <div class="media-body">
                          <div class="media-title">' . $row['first_name'] . ' ' . $row['last_name'] . ' <a href="' . base_url('users/detail/' . $row['id']) . '" target="_blank"><i class="fas fa-eye"></i></a> ' . $profile . '</div>
                          <div class="text-job text-muted">' . $row['email'] . '</div>
                        </div>
                      </li>';
            } else {
                $first_name = '<li class="media">
                        <figure class="avatar mr-2 bg-info text-white" data-initial="' . mb_substr($row['first_name'], 0, 1) . '' . mb_substr($row['last_name'], 0, 1) . '"></figure>
                        <div class="media-body">
                          <div class="media-title">' . $row['first_name'] . ' ' . $row['last_name'] . ' <a href="' . base_url('users/detail/' . $row['id']) . '" target="_blank"><i class="fas fa-eye"></i></a>' . $profile . '</div>
                          <div class="text-job text-muted">' . $row['email'] . '</div>
                        </div>
                      </li>';
            }

            $tempRow['first_name'] = $first_name;


            if ($this->ion_auth->is_admin($row['id'])) {
                $tempRow['role'] = '<div class="badge badge-secondary">Super Admin</div>';
                $action_btn = '<a class="dropdown-item has-icon make-team-member-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/remove-user-from-admin/' . $row['id']) . '"><i class="fas fa-plus"></i>' . (!empty($this->lang->line('label_make_team_member')) ? $this->lang->line('label_make_team_member') : 'Make Team Member') . '</a>';
            } elseif (is_editor($row['id'])) {
                $tempRow['role'] = '<div class="badge badge-secondary">Admin</div>';
                $action_btn = '<a class="dropdown-item has-icon make-team-member-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/remove-user-from-admin/' . $row['id']) . '"><i class="fas fa-plus"></i>' . (!empty($this->lang->line('label_make_team_member')) ? $this->lang->line('label_make_team_member') : 'Make Team Member') . '</a>';
            } else {
                if (!is_client($row['id'])) {
                    $tempRow['role'] = '<div class="badge badge-secondary">Team Member</div>';
                    $action_btn = '<a class="dropdown-item has-icon make-user-admin-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/make-user-admin/' . $row['id']) . '"><i class="fas fa-plus"></i>' . (!empty($this->lang->line('label_make_admin')) ? $this->lang->line('label_make_admin') : 'Make Admin') . '</a>
                                        <a class="dropdown-item has-icon make-user-super-admin-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/make_user_super_admin/' . $row['id']) . '"><i class="fas fa-plus"></i> ' . (!empty($this->lang->line('label_make_super_admin')) ? $this->lang->line('label_make_super_admin') : 'Make Super Admin') . '</a>';
                } else {
                    $tempRow['role'] = '<div class="badge badge-secondary">Client</div>';
                    $action_btn = '<a class="dropdown-item has-icon make-client-to-team-member-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/remove-user-from-admin/' . $row['id']) . '"><i class="fas fa-plus"></i>' . (!empty($this->lang->line('label_make_team_member')) ? $this->lang->line('label_make_team_member') : 'Make Team Member') . '</a>';
                }
            }

            if ($row['active'] == 1) {
                $active_btn = '<div class="dropdown-divider"></div>
                                      <a class="dropdown-item has-icon deactive-user-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/deactive/' . $row['id']) . '"><i class="fas fa-user-alt-slash"></i>Deactive</a>';
            } else {
                $active_btn = '<div class="dropdown-divider"></div>
                                      <a class="dropdown-item has-icon active-user-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/activate/' . $row['id']) . '"><i class="fas fa-user-check"></i>Active</a>';
            }

            if (is_admin($user_id)) {
                $action = '<div class="dropdown card-widgets">
                                  <a href="#" class="btn btn-light" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                  <div class="dropdown-menu dropdown-menu-right">
                                  ' . $action_btn . '
                                  <div class="dropdown-divider"></div>
                                      <a class="dropdown-item has-icon delete-user-alert" data-user_id="' . $row['id'] . '" href="' . base_url('users/remove-user-from-workspace/' . $row['id']) . '"><i class="far fa-trash-alt"></i>' . (!empty($this->lang->line('label_delete_from_workspace')) ? $this->lang->line('label_delete_from_workspace') : 'Delete From Workspace') . '</a>
                                      ' . $active_btn . '
                                  </div>
                              </div>';
            }

            if ($row['id'] == $user_id) {
                $action = 'You';
            }
            if (is_client($row['id'])) {
                $projects_count = get_count('id', 'projects', 'FIND_IN_SET(' . $row['id'] . ', client_id)');
                $all_projects = $this->projects_model->get_project($workspace_id, $row['id'], '', $user_type = 'client');
                $tasks_count = 0;
                $old_proj_id = '';
                foreach ($all_projects as $all_project) {
                    // if($old_proj_id != $all_project['id']){
                    // $old_proj_id = $all_project['id'];/
                    $tasks_count = $tasks_count + get_count('id', 'tasks', 'project_id=' . $all_project['id']);
                    // }
                }
            } else {
                $projects_count = get_count('id', 'projects', 'FIND_IN_SET(' . $row['id'] . ', user_id)');
                $tasks_count = get_count('id', 'tasks', 'FIND_IN_SET(' . $row['id'] . ', user_id)');
            }
            $tempRow['assigned'] = '<li class="media">
                        
                        <div class="media-items">
                          
                          <div class="media-item">
                            <div class="media-value badge badge-info">' . $projects_count . '</div>
                            <div class="media-label">Projects</div>
                          </div>
                          <div class="media-item">
                            <div class="media-value badge badge-info">' . $tasks_count . '</div>
                            <div class="media-label">Tasks</div>
                          </div>
                        </div>
                      </li>';

            $tempRow['projects'] = $projects_count;
            $tempRow['tasks'] = $tasks_count;
            $tempRow['company'] = $row['company'];
            $tempRow['phone'] = $row['phone'];
            $tempRow['active'] = ($row['active'] == 1) ? '<div class="badge badge-success">Active</div>' : '<div class="badge badge-danger">Deactive</div>';
            $tempRow['action'] = $action;

            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function update_user_lang($workspace_id, $user_id, $lang)
    {

        if ($this->db->query('UPDATE users SET lang="' . $lang . '" WHERE FIND_IN_SET(' . $workspace_id . ',workspace_id) AND id=' . $user_id . ' '))
            return true;
        else
            return false;
    }

    function add_users_ids_to_workspace($workspace_id, $user_id)
    {

        // in this func we are adding users id in the workspace - data format 1,2,3 

        $query = $this->db->query('SELECT user_id FROM workspace WHERE id=' . $workspace_id . ' ');

        if (!empty($query)) {
            foreach ($query->result_array() as $row) {
                $product_ids = $row['user_id'];
            }
            $user_id = $product_ids . ',' . $user_id;
        }

        if ($this->db->query('UPDATE workspace SET user_id="' . $user_id . '" WHERE id=' . $workspace_id . ' '))
            return true;
        else
            return false;
    }

    function make_user_admin($workspace_id, $user_id)
    {

        // in this func we are adding users id in the workspace - data format 1,2,3 

        $query = $this->db->query('SELECT admin_id FROM workspace WHERE id=' . $workspace_id . ' ');

        if (!empty($query)) {
            foreach ($query->result_array() as $row) {
                $product_ids = $row['admin_id'];
            }
            $admin_id = $product_ids . ',' . $user_id;
        }

        if ($this->db->query('UPDATE workspace SET admin_id="' . $admin_id . '" WHERE id=' . $workspace_id . ' '))
            return true;
        else
            return false;
    }

    function make_user_super_admin($user_id)
    {

        if ($this->db->query('UPDATE users_groups SET group_id=1 WHERE user_id=' . $user_id . ' '))
            return true;
        else
            return false;
    }

    function get_all_super_admins_id($group_id)
    {

        $query = $this->db->query('SELECT user_id FROM users_groups WHERE group_id=' . $group_id . ' ');

        return $query->result_array();
    }

    function remove_user_from_admin($workspace_id, $user_id, $superadmin = '')
    {

        if (!empty($superadmin)) {
            $this->db->query('UPDATE users_groups SET group_id=2 WHERE user_id=' . $user_id . ' ');
        }

        $query = $this->db->query('SELECT admin_id FROM workspace WHERE FIND_IN_SET(' . $user_id . ',`admin_id`) and id =' . $workspace_id . ' ');
        $result = $query->result_array();
        if (!empty($result)) {
            $admin_id = $result[0]['admin_id'];
            $admin_id = preg_replace('/\s+/', '', $admin_id);
            $admin_ids = explode(",", $admin_id);
            if (($key = array_search($user_id, $admin_ids)) !== false) {
                unset($admin_ids[$key]);
            }
            $admin_id = implode(",", $admin_ids);
            if ($this->db->query('UPDATE workspace SET admin_id="' . $admin_id . '" WHERE id=' . $workspace_id . ' '))
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    function remove_user_from_workspace($workspace_id, $user_id)
    {
        $this->remove_user_from_admin($workspace_id, $user_id);
        $query = $this->db->query('SELECT user_id FROM workspace WHERE FIND_IN_SET(' . $user_id . ',`user_id`) and id =' . $workspace_id . ' ');
        // print_r($this->db->last_query());
        // return false;
        $result = $query->result_array();
        if (!empty($result)) {
            $admin_id = $result[0]['user_id'];
            $admin_id = preg_replace('/\s+/', '', $admin_id);
            $admin_ids = explode(",", $admin_id);
            if (($key = array_search($user_id, $admin_ids)) !== false) {
                unset($admin_ids[$key]);
            }
            $admin_id = implode(",", $admin_ids);
            if ($this->db->query('UPDATE workspace SET user_id="' . $admin_id . '" WHERE id=' . $workspace_id . ' ')) {

                $query = $this->db->query('SELECT workspace_id FROM users WHERE FIND_IN_SET(' . $workspace_id . ',`workspace_id`) and id =' . $user_id . ' ');
                $result = $query->result_array();
                if (!empty($result)) {
                    $admin_id = $result[0]['workspace_id'];
                    $admin_id = preg_replace('/\s+/', '', $admin_id);
                    $admin_ids = explode(",", $admin_id);
                    if (($key = array_search($workspace_id, $admin_ids)) !== false) {
                        unset($admin_ids[$key]);
                    }
                    $admin_id = implode(",", $admin_ids);
                    $this->db->query('UPDATE users SET workspace_id="' . $admin_id . '" WHERE id=' . $user_id . ' ');

                    if ($this->db->query('UPDATE users SET workspace_id="' . $admin_id . '" WHERE id=' . $user_id . ' ')) {

                        $query = $this->db->query('SELECT id,user_id FROM projects WHERE FIND_IN_SET(' . $user_id . ',`user_id`) and workspace_id =' . $workspace_id . ' ');
                        $results = $query->result_array();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                $admin_id = $result['user_id'];
                                $id = $result['id'];
                                $admin_id = preg_replace('/\s+/', '', $admin_id);
                                $admin_ids = explode(",", $admin_id);
                                if (($key = array_search($user_id, $admin_ids)) !== false) {
                                    unset($admin_ids[$key]);
                                }
                                $admin_id = implode(",", $admin_ids);
                                $this->db->query('UPDATE projects SET user_id="' . $admin_id . '" WHERE id=' . $id . ' ');
                            }
                        }
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function get_user($user_id, $groups = [])
    {

        // $user_id is array of users ids 
        $this->db->select('u.*,ug.group_id');
        $this->db->from('users u');
        $this->db->where_in('u.id', $user_id);
        $this->db->join('users_groups ug', 'ug.user_id = u.id', 'left');
        if (!empty($groups)) {
            $this->db->group_start();
            foreach ($groups as $group) {
                $this->db->or_where('ug.group_id', $group);
            }
            $this->db->group_end();
        }
        $query = $this->db->get();
        return $query->result();
    }

    function get_user_array_responce($user_id)
    {

        // $user_id is array of users ids 

        $this->db->from('users');
        $this->db->where_in('id', $user_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_user_not_in_workspace($user_id)
    {

        // $user_id is array of users ids 

        $this->db->from('users');
        $this->db->where_not_in('id', $user_id);
        $query = $this->db->get();
        return $query->result();
    }

    function get_user_in_workspace($user_id, $workspace_id)
    {
        $sql = "SELECT `id` FROM `users` where id != $user_id AND FIND_IN_SET($workspace_id, workspace_id)";
        $query = $this->db->query($sql);
        $array1 = $query->result_array();
        $arr = array_map(function ($value) {
            return $value['id'];
        }, $array1);
        return $arr;
    }

    function get_users_by_email($email)
    {

        $this->db->from('users');
        $this->db->where('`email` like "%' . $email . '%" or `first_name` like "%' . $email . '%" or `last_name` like "%' . $email . '%" ');
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_users_by_email_for_add($email)
    {

        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_user_by_id($user_id, $row = false)
    {

        $this->db->from('users');
        $this->db->where('id', $user_id);
        $query = $this->db->get();
        if ($row) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }
    function get_user_by_email($email)
    {

        $this->db->from('users');
        $this->db->where('email', $email);
        // $this->db->where('email', $email);
        $query = $this->db->get();
        return $query->result_array();
    }
    function get_user_ids($user_id)
    {

        $sql = "SELECT `user_id` FROM `users_groups` where user_id != $user_id";
        $query = $this->db->query($sql);
        $array1 = $query->result_array();
        $arr = array_map(function ($value) {
            return $value['user_id'];
        }, $array1);
        return $arr;
    }

    function get_all_client_ids($group_id)
    {
        $sql = "SELECT user_id FROM users_groups WHERE group_id=" . $group_id;

        $query = $this->db->query($sql);
        $array1 = $query->result_array();
        $arr = array_map(function ($value) {
            return $value['user_id'];
        }, $array1);
        return $arr;
    }

    function get_user_emails($workspace_id)
    {
        $query = $this->db->query("SELECT email FROM users WHERE workspace_id=" . $workspace_id);
        $result = $query->result_array();
        return $result;
    }

    function get_workspcae_admin_ids($workspace_id)
    {
        $query = $this->db->query("SELECT admin_id FROM workspace WHERE id=".$workspace_id);
        $result = $query->result_array();
        return $result[0]['admin_id'];
    }
}
