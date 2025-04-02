<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model(['workspace_model', 'tasks_model', 'users_model', 'notifications_model']);
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->library('session');
	}


	public function get_tasks_list($id = '')
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$workspace_id = $this->session->userdata('workspace_id');
			$user_id = !empty($id && is_numeric($id)) ? $id : (!empty($this->uri->segment(3) && is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : $this->session->userdata('user_id'));
			return $this->tasks_model->get_tasks_list($workspace_id, $user_id);
		}
	}

	public function index()
	{
		// print_r($this->session->all_userdata());
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		} else {
			$data['user'] = $user = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();

			$product_ids = explode(',', $user->workspace_id);

			$data['workspace'] = $workspace = $this->workspace_model->get_workspace($product_ids);
			if (!empty($workspace)) {
				if (!$this->session->has_userdata('workspace_id')) {
					$this->session->set_userdata('workspace_id', $workspace[0]->id);
				}
			}

			if (!empty($this->session->has_userdata('workspace_id'))) {
				$current_workspace_id = $this->workspace_model->get_workspace($this->session->userdata('workspace_id'));

				$data['total_user'] = $this->custom_funcation_model->get_count('id', 'users_groups', 'user_id IN (' . $current_workspace_id[0]->user_id . ') AND group_id!=3');

				$data['total_client'] = $this->custom_funcation_model->get_count('id', 'users_groups', 'user_id IN (' . $current_workspace_id[0]->user_id . ') AND group_id=3');


				$user_id = $this->session->userdata('user_id');
				if (is_admin()) {
					/* for admin */
					$data['total_project'] = $total_project = $this->custom_funcation_model->get_count('id', 'projects', 'workspace_id=' . $this->session->userdata('workspace_id'));
					$data['total_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'workspace_id=' . $this->session->userdata('workspace_id'));

					$data['todo_task'] = $this->custom_funcation_model->get_count('id', '`tasks`', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="todo"');
					$data['inprogress_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="inprogress"');
					$data['review_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="review"');
					$data['done_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="done"');
				} elseif (is_member()) {
					/* for member */
					$data['total_project'] = $total_project = $this->custom_funcation_model->get_count('id', 'projects', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id'));
					$data['total_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id'));
					// print_r($this->db->last_query());
					$data['todo_task'] = $this->custom_funcation_model->get_count('id', '`tasks`', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id') . ' and status="todo"');
					$data['inprogress_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id') . ' and status="inprogress"');
					$data['review_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id') . ' and status="review"');
					$data['done_task'] = $this->custom_funcation_model->get_count('id', 'tasks', 'FIND_IN_SET(' . $user_id . ',user_id) AND workspace_id=' . $this->session->userdata('workspace_id') . ' and status="done"');
				} else {
					/* for clients */
					$data['total_project'] = $total_project = $this->custom_funcation_model->get_count('p.id', '`projects` p, users u, users_groups ug', 'u.id = ' . $user->id . ' and u.id = ug.user_id AND FIND_IN_SET( ' . $user->id . ',p.client_id ) and p.workspace_id=' . $this->session->userdata('workspace_id'));
					$data['total_task'] = $this->custom_funcation_model->get_count('t.id', '`tasks` t, projects p, users u', 'u.id = ' . $user->id . ' and FIND_IN_SET( ' . $user->id . ',p.client_id ) and p.id = t.project_id and p.workspace_id=' . $this->session->userdata('workspace_id'));

					$data['todo_task'] = $this->custom_funcation_model->get_count('t.id', '`tasks` t, projects p, users u', 'u.id = ' . $user->id . ' and FIND_IN_SET( ' . $user->id . ',p.client_id ) and p.id = t.project_id and p.workspace_id=' . $this->session->userdata('workspace_id') . ' and t.status="todo"');
					$data['inprogress_task'] = $this->custom_funcation_model->get_count('t.id', '`tasks` t, projects p, users u', 'u.id = ' . $user->id . ' and FIND_IN_SET( ' . $user->id . ',p.client_id  ) and p.id = t.project_id and p.workspace_id=' . $this->session->userdata('workspace_id') . ' and t.status="inprogress"');
					$data['review_task'] = $this->custom_funcation_model->get_count('t.id', '`tasks` t, projects p, users u', 'u.id = ' . $user->id . ' and FIND_IN_SET( ' . $user->id . ',p.client_id  ) and p.id = t.project_id and p.workspace_id=' . $this->session->userdata('workspace_id') . ' and t.status="review"');
					$data['done_task'] = $this->custom_funcation_model->get_count('t.id', '`tasks` t, projects p, users u', 'u.id = ' . $user->id . ' and FIND_IN_SET( ' . $user->id . ',p.client_id  ) and p.id = t.project_id and p.workspace_id=' . $this->session->userdata('workspace_id') . ' and t.status="done"');

					$data['total_notes'] = $this->custom_funcation_model->get_count('id', 'notes', 'user_id = ' . $user->id . ' and workspace_id=' . $this->session->userdata('workspace_id') . ' ');
				}

				$data['notes'] = $notes = $this->custom_funcation_model->get_count('id', 'notes', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and user_id=' . $this->session->userdata('user_id') . '');
			} else {
				$data['total_user'] = 0;

				$data['total_client'] = 0;

				$data['total_task'] = 0;

				$data['todo_task'] = 0;

				$data['inprogress_task'] = 0;

				$data['review_task'] = 0;

				$data['done_task'] = 0;

				$data['total_project'] = $total_project = 0;

				$data['total_notes'] = $notes = 0;
			}

			if ($total_project != 0) {
				if (is_admin()) {
					/* If he is an admin */
					$finished_project = $this->custom_funcation_model->get_count('id', 'projects', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="finished"');
					$data['finished_project_count'] = $finished_project;
					$finished_project = $finished_project * 100 / $total_project;
					$data['finished_project'] = bcdiv($finished_project, 1, 2);

					$ongoing_project = $this->custom_funcation_model->get_count('id', 'projects', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="ongoing"');
					$data['ongoing_project_count'] = $ongoing_project;
					$ongoing_project =  $ongoing_project * 100 / $total_project;
					$data['ongoing_project'] = bcdiv($ongoing_project, 1, 2);

					$onhold_project = $this->custom_funcation_model->get_count('id', 'projects', 'workspace_id=' . $this->session->userdata('workspace_id') . ' and status="onhold"');
					$data['onhold_project_count'] = $onhold_project;
					$onhold_project = $onhold_project * 100 / $total_project;
					$data['onhold_project'] = bcdiv($onhold_project, 1, 2);
				} elseif (is_member()) {
					/* If he is an team member */
					$finished_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`user_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="finished"');
					$data['finished_project_count'] = $finished_project;
					$finished_project = $finished_project * 100 / $total_project;
					$data['finished_project'] = bcdiv($finished_project, 1, 2);

					$ongoing_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`user_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="ongoing"');
					$data['ongoing_project_count'] = $ongoing_project;
					$ongoing_project =  $ongoing_project * 100 / $total_project;
					$data['ongoing_project'] = bcdiv($ongoing_project, 1, 2);

					$onhold_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`user_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="onhold"');
					$data['onhold_project_count'] = $onhold_project;
					$onhold_project = $onhold_project * 100 / $total_project;
					$data['onhold_project'] = bcdiv($onhold_project, 1, 2);
				} 
				else {
					/* if he is a client */
					$finished_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`client_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="finished"');
					$data['finished_project_count'] = $finished_project;
					$finished_project = $finished_project * 100 / $total_project;
					$data['finished_project'] = bcdiv($finished_project, 1, 2);

					$ongoing_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`client_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="ongoing"');
					$data['ongoing_project_count'] = $ongoing_project;
					$ongoing_project =  $ongoing_project * 100 / $total_project;
					$data['ongoing_project'] = bcdiv($ongoing_project, 1, 2);

					$onhold_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`client_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="onhold"');
					$data['onhold_project_count'] = $onhold_project;
					$onhold_project = $onhold_project * 100 / $total_project;
					$data['onhold_project'] = bcdiv($onhold_project, 1, 2);

					$notstarted_project = $this->custom_funcation_model->get_count('id', 'projects', ' FIND_IN_SET(' . $user->id . ',`client_id` ) and workspace_id=' . $this->session->userdata('workspace_id') . ' and status="notstarted"');
					$data['notstarted_project_count'] = $notstarted_project;
					$notstarted_project = $notstarted_project * 100 / $total_project;
					$data['notstarted_project'] = bcdiv($notstarted_project, 1, 2);
				}
			} else {
				$data['finished_project'] = 0;
				$data['finished_project_count'] = 0;
				$data['ongoing_project'] = 0;
				$data['ongoing_project_count'] = 0;
				$data['onhold_project'] = 0;
				$data['onhold_project_count'] = 0;
			}

			// if (!empty($workspace_id)) {
			$data['notifications'] = !empty($workspace_id) ? $this->notifications_model->get_notifications($this->session->userdata['user_id'], $workspace_id) : array();
			$data['is_admin'] =  $this->ion_auth->is_admin();
			$this->load->view('home', $data);
			// } else {
			// 	redirect('home', 'refresh');
			// }
		}
	}
}
