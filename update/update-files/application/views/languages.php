<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Languages &mdash; <?= get_compnay_title(); ?></title>
    <?php include('include-css.php'); ?>
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php include('include-header.php'); ?>
            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1><?= !empty($this->lang->line('label_languages')) ? $this->lang->line('label_languages') : 'Languages'; ?>
                        </h1>
                        <div class="section-header-breadcrumb">
                            <i class="btn btn-primary btn-rounded no-shadow" id="modal-add-language"><?= !empty($this->lang->line('label_create_new')) ? $this->lang->line('label_create_new') : 'Create New'; ?></i>
                        </div>
                    </div>
                    <div class="section-body">
                        <div id="output-status"></div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><?= !empty($this->lang->line('label_jump_to')) ? $this->lang->line('label_jump_to') : 'Jump To'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                                            <?php
                                            $languages =  get_languages();
                                            foreach ($languages as $lang) {
                                                if ($lang['language'] == $active_tab_lang) { ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href='<?= base_url("languages/change/" . $lang['language']); ?>'><?= ucfirst($lang['language']); ?></a>
                                                    </li>
                                                <?php } else { ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href='<?= base_url("languages/change/" . $lang['language']); ?>'><?= ucfirst($lang['language']); ?></a>
                                                    </li>
                                            <?php }
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="tab-content no-padding" id="myTab2Content">
                                    <div class="tab-pane fade show active" id="languages-settings" role="tabpanel" aria-labelledby="languages-tab4">
                                        <form action="<?= base_url('languages/save_languages'); ?>" id="languages-setting-form">
                                            <div class="card" id="languages-settings-card">
                                                <div class="card-header">
                                                    <h4>Labels</h4>
                                                    <div class="card-header-action float-right">
                                                        <div class="card-widgets form-check">
                                                            <input class="form-check-input" name="is_rtl" type="checkbox" id="is_rtlCheck1" <?= !empty($rtl) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="is_rtlCheck1">
                                                                Enable RTL
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>Dashboard</label>
                                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" class="form-control" value="<?= $this->security->get_csrf_hash(); ?>">
                                                            <input type="hidden" name="language" value='<?= $this->lang->line('label_language') ?>'>
                                                            <input type="text" name="dashboard" value='<?= $this->lang->line('label_dashboard') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Projects</label>
                                                            <input type="text" name="projects" value='<?= $this->lang->line('label_projects') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Users</label>
                                                            <input type="text" name="users" value='<?= $this->lang->line('label_users') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Chat</label>
                                                            <input type="text" name="chat" value='<?= $this->lang->line('label_chat') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Notes</label>
                                                            <input type="text" name="notes" value='<?= $this->lang->line('label_notes') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Settings</label>
                                                            <input type="text" name="settings" value='<?= $this->lang->line('label_settings') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Notifications</label>
                                                            <input type="text" name="notifications" value='<?= $this->lang->line('label_notifications') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Calendar</label>
                                                            <input type="text" name="calendar" value='<?= $this->lang->line('label_calendar') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Estimates</label>
                                                            <input type="text" name="estimates" value='<?= $this->lang->line('label_estimates') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoices</label>
                                                            <input type="text" name="invoices" value='<?= $this->lang->line('label_invoices') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Items</label>
                                                            <input type="text" name="items" value='<?= $this->lang->line('label_items') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Estimate</label>
                                                            <input type="text" name="estimate" value='<?= $this->lang->line('label_estimate') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Finance</label>
                                                            <input type="text" name="finance" value='<?= $this->lang->line('label_finance') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expenses</label>
                                                            <input type="text" name="expenses" value='<?= $this->lang->line('label_expenses') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Taxes</label>
                                                            <input type="text" name="taxes" value='<?= $this->lang->line('label_taxes') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Units</label>
                                                            <input type="text" name="units" value='<?= $this->lang->line('label_units') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Activity Logs</label>
                                                            <input type="text" name="activity_logs" value='<?= $this->lang->line('label_activity_logs') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Events</label>
                                                            <input type="text" name="events" value='<?= $this->lang->line('label_events') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Announcements</label>
                                                            <input type="text" name="announcements" value='<?= $this->lang->line('label_announcements') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Total Projects</label>
                                                            <input type="text" name="total_projects" value='<?= $this->lang->line('label_total_projects') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Total Tasks</label>
                                                            <input type="text" name="total_tasks" value='<?= $this->lang->line('label_total_tasks') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Project Status</label>
                                                            <input type="text" name="project_status" value='<?= $this->lang->line('label_project_status') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Task Overview</label>
                                                            <input type="text" name="task_overview" value='<?= $this->lang->line('label_task_overview') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Task Insights</label>
                                                            <input type="text" name="task_insights" value='<?= $this->lang->line('label_task_insights') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Ongoing</label>
                                                            <input type="text" name="ongoing" value='<?= $this->lang->line('label_ongoing') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Finished</label>
                                                            <input type="text" name="finished" value='<?= $this->lang->line('label_finished') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Onhold</label>
                                                            <input type="text" name="onhold" value='<?= $this->lang->line('label_onhold') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Todo</label>
                                                            <input type="text" name="todo" value='<?= $this->lang->line('label_todo') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>In Progress</label>
                                                            <input type="text" name="inprogress" value='<?= $this->lang->line('label_inprogress') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Review</label>
                                                            <input type="text" name="review" value='<?= $this->lang->line('label_review') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Done</label>
                                                            <input type="text" name="done" value='<?= $this->lang->line('label_done') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>No task assigned</label>
                                                            <input type="text" name="no_task_assigned" value='<?= $this->lang->line('label_no_task_assigned') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Owner</label>
                                                            <input type="text" name="owner" value='<?= $this->lang->line('label_owner') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create New Workspace</label>
                                                            <input type="text" name="create_new_workspace" value='<?= $this->lang->line('label_create_new_workspace') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Remove Me From Workspace</label>
                                                            <input type="text" name="remove_me_from_workspace" value='<?= $this->lang->line('label_remove_me_from_workspace') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Profile</label>
                                                            <input type="text" name="profile" value='<?= $this->lang->line('label_profile') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Logout</label>
                                                            <input type="text" name="logout" value='<?= $this->lang->line('label_logout') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>All</label>
                                                            <input type="text" name="all" value='<?= $this->lang->line('label_all') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Tasks</label>
                                                            <input type="text" name="tasks" value='<?= $this->lang->line('label_tasks') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Comments</label>
                                                            <input type="text" name="comments" value='<?= $this->lang->line('label_comments') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Details</label>
                                                            <input type="text" name="details" value='<?= $this->lang->line('label_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Delete</label>
                                                            <input type="text" name="delete" value='<?= $this->lang->line('label_delete') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Project</label>
                                                            <input type="text" name="create_project" value='<?= $this->lang->line('label_create_project') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Start Date</label>
                                                            <input type="text" name="start_date" value='<?= $this->lang->line('label_start_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>End Date</label>
                                                            <input type="text" name="end_date" value='<?= $this->lang->line('label_end_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Budget</label>
                                                            <input type="text" name="budget" value='<?= $this->lang->line('label_budget') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Days Left</label>
                                                            <input type="text" name="days_left" value='<?= $this->lang->line('label_days_left') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Upload Files</label>
                                                            <input type="text" name="upload_files" value='<?= $this->lang->line('label_upload_files') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Drop Files Here To Upload</label>
                                                            <input type="text" name="drop_files_here_to_upload" value='<?= $this->lang->line('label_drop_files_here_to_upload') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Drop File Here To Upload</label>
                                                            <input type="text" name="drop_file_here_to_upload" value='<?= $this->lang->line('label_drop_file_here_to_upload') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Uploaded Files</label>
                                                            <input type="text" name="uploaded_files" value='<?= $this->lang->line('label_uploaded_files') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Preview</label>
                                                            <input type="text" name="preview" value='<?= $this->lang->line('label_preview') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Name</label>
                                                            <input type="text" name="name" value='<?= $this->lang->line('label_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Size</label>
                                                            <input type="text" name="size" value='<?= $this->lang->line('label_size') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Action</label>
                                                            <input type="text" name="action" value='<?= $this->lang->line('label_action') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Milestones</label>
                                                            <input type="text" name="milestones" value='<?= $this->lang->line('label_milestones') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Title</label>
                                                            <input type="text" name="title" value='<?= $this->lang->line('label_title') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Status</label>
                                                            <input type="text" name="status" value='<?= $this->lang->line('label_status') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Active</label>
                                                            <input type="text" name="active" value='<?= $this->lang->line('label_active') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Deactive</label>
                                                            <input type="text" name="deactive" value='<?= $this->lang->line('label_deactive') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Summary</label>
                                                            <input type="text" name="summary" value='<?= $this->lang->line('label_summary') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Cost</label>
                                                            <input type="text" name="cost" value='<?= $this->lang->line('label_cost') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create</label>
                                                            <input type="text" name="create" value='<?= $this->lang->line('label_create') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Incomplete</label>
                                                            <input type="text" name="incomplete" value='<?= $this->lang->line('label_incomplete') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Complete</label>
                                                            <input type="text" name="complete" value='<?= $this->lang->line('label_complete') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Description</label>
                                                            <input type="text" name="description" value='<?= $this->lang->line('label_description') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Update</label>
                                                            <input type="text" name="update" value='<?= $this->lang->line('label_update') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Edit</label>
                                                            <input type="text" name="edit" value='<?= $this->lang->line('label_edit') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Project Tasks</label>
                                                            <input type="text" name="project_tasks" value='<?= $this->lang->line('label_project_tasks') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Task</label>
                                                            <input type="text" name="create_task" value='<?= $this->lang->line('label_create_task') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Milestone</label>
                                                            <input type="text" name="milestone" value='<?= $this->lang->line('label_milestone') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Priority</label>
                                                            <input type="text" name="priority" value='<?= $this->lang->line('label_priority') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Low</label>
                                                            <input type="text" name="low" value='<?= $this->lang->line('label_low') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Medium</label>
                                                            <input type="text" name="medium" value='<?= $this->lang->line('label_medium') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>High</label>
                                                            <input type="text" name="high" value='<?= $this->lang->line('label_high') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Assign To</label>
                                                            <input type="text" name="assign_to" value='<?= $this->lang->line('label_assign_to') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Due Date</label>
                                                            <input type="text" name="due_date" value='<?= $this->lang->line('label_due_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>From Date</label>
                                                            <input type="text" name="from_date" value='<?= $this->lang->line('label_from_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Date</label>
                                                            <input type="text" name="date" value='<?= $this->lang->line('label_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Files</label>
                                                            <input type="text" name="files" value='<?= $this->lang->line('label_files') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Submit</label>
                                                            <input type="text" name="submit" value='<?= $this->lang->line('label_submit') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Admin</label>
                                                            <input type="text" name="admin" value='<?= $this->lang->line('label_admin') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Team Member</label>
                                                            <input type="text" name="team_member" value='<?= $this->lang->line('label_team_member') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Contact</label>
                                                            <input type="text" name="contact" value='<?= $this->lang->line('label_contact') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add User</label>
                                                            <input type="text" name="add_user" value='<?= $this->lang->line('label_add_user') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Make Admin</label>
                                                            <input type="text" name="make_admin" value='<?= $this->lang->line('label_make_admin') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Delete From Workspace</label>
                                                            <input type="text" name="delete_from_workspace" value='<?= $this->lang->line('label_delete_from_workspace') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Email</label>
                                                            <input type="text" name="email" value='<?= $this->lang->line('label_email') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>First Name</label>
                                                            <input type="text" name="first_name" value='<?= $this->lang->line('label_first_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Last Name</label>
                                                            <input type="text" name="last_name" value='<?= $this->lang->line('label_last_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Password</label>
                                                            <input type="text" name="password" value='<?= $this->lang->line('label_password') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Confirm Password</label>
                                                            <input type="text" name="confirm_password" value='<?= $this->lang->line('label_confirm_password') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add</label>
                                                            <input type="text" name="add" value='<?= $this->lang->line('label_add') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Chat Box</label>
                                                            <input type="text" name="chat_box" value='<?= $this->lang->line('label_chat_box') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Personal Chat</label>
                                                            <input type="text" name="personal_chat" value='<?= $this->lang->line('label_personal_chat') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Group Chat</label>
                                                            <input type="text" name="group_chat" value='<?= $this->lang->line('label_group_chat') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Close</label>
                                                            <input type="text" name="close" value='<?= $this->lang->line('label_close') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Search</label>
                                                            <input type="text" name="search" value='<?= $this->lang->line('label_search') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Search All</label>
                                                            <input type="text" name="search_all" value='<?= $this->lang->line('label_search_all') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Group</label>
                                                            <input type="text" name="create_group" value='<?= $this->lang->line('label_create_group') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Users</label>
                                                            <input type="text" name="select_users" value='<?= $this->lang->line('label_select_users') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Color</label>
                                                            <input type="text" name="color" value='<?= $this->lang->line('label_color') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>OK</label>
                                                            <input type="text" name="ok" value='<?= $this->lang->line('label_ok') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Cancel</label>
                                                            <input type="text" name="cancel" value='<?= $this->lang->line('label_cancel') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Your Data is safe</label>
                                                            <input type="text" name="your_data_is_safe" value='<?= $this->lang->line('label_your_data_is_safe') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Note</label>
                                                            <input type="text" name="add_note" value='<?= $this->lang->line('label_add_note') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Note</label>
                                                            <input type="text" name="create_note" value='<?= $this->lang->line('label_create_note') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Info</label>
                                                            <input type="text" name="info" value='<?= $this->lang->line('label_info') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Warning</label>
                                                            <input type="text" name="warning" value='<?= $this->lang->line('label_warning') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Danger</label>
                                                            <input type="text" name="danger" value='<?= $this->lang->line('label_danger') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>General</label>
                                                            <input type="text" name="general" value='<?= $this->lang->line('label_general') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>System</label>
                                                            <input type="text" name="system" value='<?= $this->lang->line('label_system') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Change Settings</label>
                                                            <input type="text" name="change_settings" value='<?= $this->lang->line('label_change_settings') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>General Settings</label>
                                                            <input type="text" name="general_settings" value='<?= $this->lang->line('label_general_settings') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Company Title</label>
                                                            <input type="text" name="company_title" value='<?= $this->lang->line('label_company_title') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Full Logo</label>
                                                            <input type="text" name="full_logo" value='<?= $this->lang->line('label_full_logo') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Half Logo</label>
                                                            <input type="text" name="half_logo" value='<?= $this->lang->line('label_half_logo') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Favicon</label>
                                                            <input type="text" name="favicon" value='<?= $this->lang->line('label_favicon') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Chat Theme</label>
                                                            <input type="text" name="chat_theme" value='<?= $this->lang->line('label_chat_theme') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Google Analytics Code</label>
                                                            <input type="text" name="google_analytics_code" value='<?= $this->lang->line('label_google_analytics_code') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Save Changes</label>
                                                            <input type="text" name="save_changes" value='<?= $this->lang->line('label_save_changes') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Email Settings</label>
                                                            <input type="text" name="email_settings" value='<?= $this->lang->line('label_email_settings') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>SMTP Host</label>
                                                            <input type="text" name="smtp_host" value='<?= $this->lang->line('label_smtp_host') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>SMTP Port</label>
                                                            <input type="text" name="smtp_port" value='<?= $this->lang->line('label_smtp_port') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Email Content Type</label>
                                                            <input type="text" name="email_content_type" value='<?= $this->lang->line('label_email_content_type') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>SMTP Encryption</label>
                                                            <input type="text" name="smtp_encryption" value='<?= $this->lang->line('label_smtp_encryption') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Web API Key</label>
                                                            <input type="text" name="web_api_key" value='<?= $this->lang->line('label_web_api_key') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Project ID</label>
                                                            <input type="text" name="project_id" value='<?= $this->lang->line('label_project_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Sender ID</label>
                                                            <input type="text" name="sender_id" value='<?= $this->lang->line('label_sender_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create & Customize</label>
                                                            <input type="text" name="create_and_customize" value='<?= $this->lang->line('label_create_and_customize') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Hi</label>
                                                            <input type="text" name="hi" value='<?= $this->lang->line('label_hi') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Jump To</label>
                                                            <input type="text" name="jump_to" value='<?= $this->lang->line('label_jump_to') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Project Details</label>
                                                            <input type="text" name="project_details" value='<?= $this->lang->line('label_project_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Date</label>
                                                            <input type="text" name="create_date" value='<?= $this->lang->line('label_create_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Make Team Member</label>
                                                            <input type="text" name="make_team_member" value='<?= $this->lang->line('label_make_team_member') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Admins</label>
                                                            <input type="text" name="select_admins" value='<?= $this->lang->line('label_select_admins') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Group Admins</label>
                                                            <input type="text" name="group_admins" value='<?= $this->lang->line('label_group_admins') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Search Result</label>
                                                            <input type="text" name="search_result" value='<?= $this->lang->line('label_search_result') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Choose File</label>
                                                            <input type="text" name="choose_file" value='<?= $this->lang->line('label_choose_file') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>System Settings</label>
                                                            <input type="text" name="system_settings" value='<?= $this->lang->line('label_system_settings') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Languages</label>
                                                            <input type="text" name="languages" value='<?= $this->lang->line('label_languages') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create New</label>
                                                            <input type="text" name="create_new" value='<?= $this->lang->line('label_create_new') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Timezone</label>
                                                            <input type="text" name="timezone" value='<?= $this->lang->line('label_timezone') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>App URL</label>
                                                            <input type="text" name="app_url" value='<?= $this->lang->line('label_app_url') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Announcement</label>
                                                            <input type="text" name="announcement" value='<?= $this->lang->line('label_announcement') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Announcement Details</label>
                                                            <input type="text" name="announcement_details" value='<?= $this->lang->line('label_announcement_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Announcement</label>
                                                            <input type="text" name="create_announcement" value='<?= $this->lang->line('label_create_announcement') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Pinned Announcement</label>
                                                            <input type="text" name="pinned_announcement" value='<?= $this->lang->line('label_pinned_announcement') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Unpinned Announcement</label>
                                                            <input type="text" name="unpinned_announcement" value='<?= $this->lang->line('label_unpinned_announcement') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Filter</label>
                                                            <input type="text" name="filter" value='<?= $this->lang->line('label_filter') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Mark All As Read</label>
                                                            <input type="text" name="mark_all_as_read" value='<?= $this->lang->line('label_mark_all_as_read') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>No Unread Notifications Found</label>
                                                            <input type="text" name="no_unread_notifications_found" value='<?= $this->lang->line('label_no_unread_notifications_found') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>View All</label>
                                                            <input type="text" name="view_all" value='<?= $this->lang->line('label_view_all') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Notification Details</label>
                                                            <input type="text" name="notification_details" value='<?= $this->lang->line('label_notification_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Estimate</label>
                                                            <input type="text" name="add_estimate" value='<?= $this->lang->line('label_add_estimate') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Invoice</label>
                                                            <input type="text" name="add_invoice" value='<?= $this->lang->line('label_add_invoice') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Edit Estimate</label>
                                                            <input type="text" name="edit_estimate" value='<?= $this->lang->line('label_edit_estimate') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>View Estimate</label>
                                                            <input type="text" name="view_estimate" value='<?= $this->lang->line('label_view_estimate') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Client</label>
                                                            <input type="text" name="select_client" value='<?= $this->lang->line('label_select_client') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Draft</label>
                                                            <input type="text" name="draft" value='<?= $this->lang->line('label_draft') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Fully Paid</label>
                                                            <input type="text" name="fully_paid" value='<?= $this->lang->line('label_fully_paid') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Partially Paid</label>
                                                            <input type="text" name="partially_paid" value='<?= $this->lang->line('label_partially_paid') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Sent</label>
                                                            <input type="text" name="sent" value='<?= $this->lang->line('label_sent') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Failed</label>
                                                            <input type="text" name="failed" value='<?= $this->lang->line('label_failed') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Attachments</label>
                                                            <input type="text" name="attachments" value='<?= $this->lang->line('label_attachments') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Send mail</label>
                                                            <input type="text" name="send_mail" value='<?= $this->lang->line('label_send_mail') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Save as draft</label>
                                                            <input type="text" name="save_as_draft" value='<?= $this->lang->line('label_save_as_draft') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Retry</label>
                                                            <input type="text" name="retry" value='<?= $this->lang->line('label_retry') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>No attachments found</label>
                                                            <input type="text" name="no_attachments_found" value='<?= $this->lang->line('label_no_attachments_found') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Mail details</label>
                                                            <input type="text" name="mail_details" value='<?= $this->lang->line('label_mail_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expired</label>
                                                            <input type="text" name="expired" value='<?= $this->lang->line('label_expired') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Due</label>
                                                            <input type="text" name="due" value='<?= $this->lang->line('label_due') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Declined</label>
                                                            <input type="text" name="declined" value='<?= $this->lang->line('label_declined') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Accepted</label>
                                                            <input type="text" name="accepted" value='<?= $this->lang->line('label_accepted') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Billing Details</label>
                                                            <input type="text" name="billing_details" value='<?= $this->lang->line('label_billing_details') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Note</label>
                                                            <input type="text" name="note" value='<?= $this->lang->line('label_note') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Estimate Date</label>
                                                            <input type="text" name="estimate_date" value='<?= $this->lang->line('label_estimate_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoice Date</label>
                                                            <input type="text" name="invoice_date" value='<?= $this->lang->line('label_invoice_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Estimate No</label>
                                                            <input type="text" name="estimate_no" value='<?= $this->lang->line('label_estimate_no') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expiry Date</label>
                                                            <input type="text" name="expiry_date" value='<?= $this->lang->line('label_expiry_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Choose Item</label>
                                                            <input type="text" name="choose_item" value='<?= $this->lang->line('label_choose_item') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Item</label>
                                                            <input type="text" name="add_item" value='<?= $this->lang->line('label_add_item') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Product / Service</label>
                                                            <input type="text" name="product_service" value='<?= $this->lang->line('label_product_service') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Quantity</label>
                                                            <input type="text" name="quantity" value='<?= $this->lang->line('label_quantity') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Unit</label>
                                                            <input type="text" name="unit" value='<?= $this->lang->line('label_unit') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Rate</label>
                                                            <input type="text" name="rate" value='<?= $this->lang->line('label_rate') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Price</label>
                                                            <input type="text" name="price" value='<?= $this->lang->line('label_price') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Tax</label>
                                                            <input type="text" name="tax" value='<?= $this->lang->line('label_tax') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoice Summary</label>
                                                            <input type="text" name="invoice_summary" value='<?= $this->lang->line('label_invoice_summary') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payment Summary</label>
                                                            <input type="text" name="payment_summary" value='<?= $this->lang->line('label_payment_summary') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>No Payments Found</label>
                                                            <input type="text" name="no_payments_found" value='<?= $this->lang->line('label_no_payments_found') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Amount</label>
                                                            <input type="text" name="amount" value='<?= $this->lang->line('label_amount') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>N/A</label>
                                                            <input type="text" name="n_a" value='<?= $this->lang->line('label_n_a') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Address</label>
                                                            <input type="text" name="address" value='<?= $this->lang->line('label_address') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>City</label>
                                                            <input type="text" name="city" value='<?= $this->lang->line('label_city') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>State</label>
                                                            <input type="text" name="state" value='<?= $this->lang->line('label_state') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Country</label>
                                                            <input type="text" name="country" value='<?= $this->lang->line('label_country') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Zip Code</label>
                                                            <input type="text" name="zip_code" value='<?= $this->lang->line('label_zip_code') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expense Type ID</label>
                                                            <input type="text" name="expense_type_id" value='<?= $this->lang->line('label_expense_type_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>User Name</label>
                                                            <input type="text" name="user_name" value='<?= $this->lang->line('label_user_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expense Type</label>
                                                            <input type="text" name="expense_type" value='<?= $this->lang->line('label_expense_type') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expense Date</label>
                                                            <input type="text" name="expense_date" value='<?= $this->lang->line('label_expense_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Expense Type</label>
                                                            <input type="text" name="add_expense_type" value='<?= $this->lang->line('label_add_expense_type') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Expense</label>
                                                            <input type="text" name="add_expense" value='<?= $this->lang->line('label_add_expense') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>User ID</label>
                                                            <input type="text" name="user_id" value='<?= $this->lang->line('label_user_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Expense Title</label>
                                                            <input type="text" name="title" value='<?= $this->lang->line('label_title') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Choose</label>
                                                            <input type="text" name="choose" value='<?= $this->lang->line('label_choose') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add New Type</label>
                                                            <input type="text" name="add_new_type" value='<?= $this->lang->line('label_add_new_type') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Percentage</label>
                                                            <input type="text" name="percentage" value='<?= $this->lang->line('label_add_new_type') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Subtotal</label>
                                                            <input type="text" name="sub_total" value='<?= $this->lang->line('label_subtotal') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Total</label>
                                                            <input type="text" name="total" value='<?= $this->lang->line('label_total') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Final Total</label>
                                                            <input type="text" name="final_total" value='<?= $this->lang->line('label_final_total') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Sub Total</label>
                                                            <input type="text" name="sub_total" value='<?= $this->lang->line('label_sub_total') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Total Tax</label>
                                                            <input type="text" name="total_tax" value='<?= $this->lang->line('label_total_tax') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Personal Note</label>
                                                            <input type="text" name="personal_note" value='<?= $this->lang->line('label_personal_note') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payments</label>
                                                            <input type="text" name="payments" value='<?= $this->lang->line('label_payments') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payment Modes</label>
                                                            <input type="text" name="payment_modes" value='<?= $this->lang->line('label_payment_modes') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Payment</label>
                                                            <input type="text" name="add_payment" value='<?= $this->lang->line('label_add_payments') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Create Payment</label>
                                                            <input type="text" name="create_payment" value='<?= $this->lang->line('label_create_payments') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Payment Mode</label>
                                                            <input type="text" name="add_payment_mode" value='<?= $this->lang->line('label_add_payment_mode') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Invoice</label>
                                                            <input type="text" name="select_invoice" value='<?= $this->lang->line('label_select_invoice') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payment Date</label>
                                                            <input type="text" name="payment_date" value='<?= $this->lang->line('label_payment_date') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payment Mode</label>
                                                            <input type="text" name="payment_mode" value='<?= $this->lang->line('label_payment_mode') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoice ID</label>
                                                            <input type="text" name="invoice_id" value='<?= $this->lang->line('label_invoice_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Not assigned</label>
                                                            <input type="text" name="not_assigned" value='<?= $this->lang->line('label_not_assigned') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Payment Mode ID</label>
                                                            <input type="text" name="payment_mode_id" value='<?= $this->lang->line('label_payment_mode_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Client ID</label>
                                                            <input type="text" name="client_id" value='<?= $this->lang->line('label_client_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Client Name</label>
                                                            <input type="text" name="client_name" value='<?= $this->lang->line('label_client_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoice</label>
                                                            <input type="text" name="invoice" value='<?= $this->lang->line('label_invoice') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Invoice No</label>
                                                            <input type="text" name="invoice_no" value='<?= $this->lang->line('label_invoice_no') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Edit Invoice</label>
                                                            <input type="text" name="edit_invoice" value='<?= $this->lang->line('label_edit_invoice') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Unit ID</label>
                                                            <input type="text" name="unit_id" value='<?= $this->lang->line('label_unit_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Currency</label>
                                                            <input type="text" name="currency" value='<?= $this->lang->line('label_currency') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Currency Symbol</label>
                                                            <input type="text" name="currency_symbol" value='<?= $this->lang->line('label_currency_symbol') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Currency Shortcode</label>
                                                            <input type="text" name="currency_shortcode" value='<?= $this->lang->line('label_currency_shortcode') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Currency Full Form</label>
                                                            <input type="text" name="currency_full_form" value='<?= $this->lang->line('label_currency_full_form') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Edit Workspace</label>
                                                            <input type="text" name="edit_workspace" value='<?= $this->lang->line('label_edit_workspace') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Manage Workspaces</label>
                                                            <input type="text" name="label_manage_workspaces" value='<?= $this->lang->line('label_manage_workspaces') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Workspaces</label>
                                                            <input type="text" name="label_workspaces" value='<?= $this->lang->line('label_workspaces') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Project Name</label>
                                                            <input type="text" name="project_name" value='<?= $this->lang->line('label_project_name') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Status</label>
                                                            <input type="text" name="select_status" value='<?= $this->lang->line('label_select_status') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Tasks Due Dates Between</label>
                                                            <input type="text" name="tasks_due_dates_between" value='<?= $this->lang->line('label_tasks_due_dates_between') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>ID</label>
                                                            <input type="text" name="id" value='<?= $this->lang->line('label_id') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Leave Requests</label>
                                                            <input type="text" name="leave_requests" value='<?= $this->lang->line('label_leave_requests') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Leaves</label>
                                                            <input type="text" name="leaves" value='<?= $this->lang->line('label_leaves') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Request Leave</label>
                                                            <input type="text" name="request_leave" value='<?= $this->lang->line('label_request_leave') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Leave Duration</label>
                                                            <input type="text" name="leave_duration" value='<?= $this->lang->line('label_leave_duration') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Reason</label>
                                                            <input type="text" name="reason" value='<?= $this->lang->line('label_reason') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Action By</label>
                                                            <input type="text" name="action_by" value='<?= $this->lang->line('label_action_by') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Grid View</label>
                                                            <input type="text" name="grid_view" value='<?= $this->lang->line('label_grid_view') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>List View</label>
                                                            <input type="text" name="list_view" value='<?= $this->lang->line('label_list_view') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Role</label>
                                                            <input type="text" name="role" value='<?= $this->lang->line('label_role') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Assigned</label>
                                                            <input type="text" name="assigned" value='<?= $this->lang->line('label_assigned') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Hide Budget/Costs From Users?</label>
                                                            <input type="text" name="hide_budget_costs_from_users" value='<?= $this->lang->line('label_hide_budget_costs_from_users') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Version</label>
                                                            <input type="text" name="version" value='<?= $this->lang->line('label_version') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Leave Date From To</label>
                                                            <input type="text" name="leave_date_from_to" value='<?= $this->lang->line('label_leave_date_from_to') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>No. of Days</label>
                                                            <input type="text" name="no_of_days" value='<?= $this->lang->line('label_no_of_days') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Assign users to manage leave requests</label>
                                                            <input type="text" name="assign_users_to_manage_leave_requests" value='<?= $this->lang->line('label_assign_users_to_manage_leave_requests') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Approve</label>
                                                            <input type="text" name="approve" value='<?= $this->lang->line('label_approve') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Disapprove</label>
                                                            <input type="text" name="disapprove" value='<?= $this->lang->line('label_disapprove') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Approved</label>
                                                            <input type="text" name="approved" value='<?= $this->lang->line('label_approved') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Disapproved</label>
                                                            <input type="text" name="disapproved" value='<?= $this->lang->line('label_disapproved') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Under Review</label>
                                                            <input type="text" name="under_review" value='<?= $this->lang->line('label_under_review') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Reason for leave?</label>
                                                            <input type="text" name="reason_for_leave" value='<?= $this->lang->line('label_reason_for_leave') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Milestone</label>
                                                            <input type="text" name="select_milestone" value='<?= $this->lang->line('label_select_milestone') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Not Started</label>
                                                            <input type="text" name="notstarted" value='<?= $this->lang->line('label_notstarted') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Cancelled</label>
                                                            <input type="text" name="cancelled" value='<?= $this->lang->line('label_cancelled') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Clients</label>
                                                            <input type="text" name="clients" value='<?= $this->lang->line('label_clients') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Client</label>
                                                            <input type="text" name="client" value='<?= $this->lang->line('label_client') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Add Client</label>
                                                            <input type="text" name="add_client" value='<?= $this->lang->line('label_add_client') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Select Clients</label>
                                                            <input type="text" name="select_clients" value='<?= $this->lang->line('label_select_clients') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>System Fonts</label>
                                                            <input type="text" name="system_fonts" value='<?= $this->lang->line('label_system_fonts') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Manage</label>
                                                            <input type="text" name="manage" value='<?= $this->lang->line('label_manage') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Company</label>
                                                            <input type="text" name="company" value='<?= $this->lang->line('label_company') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Phone</label>
                                                            <input type="text" name="phone" value='<?= $this->lang->line('label_phone') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Font Format</label>
                                                            <input type="text" name="font_format" value='<?= $this->lang->line('label_font_format') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Send mail</label>
                                                            <input type="text" name="send_mail" value='<?= $this->lang->line('label_send_mail') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Subject</label>
                                                            <input type="text" name="subject" value='<?= $this->lang->line('label_subject') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Message</label>
                                                            <input type="text" name="message" value='<?= $this->lang->line('label_message') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Type your message</label>
                                                            <input type="text" name="type_your_message" value='<?= $this->lang->line('label_type_your_message') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>To</label>
                                                            <input type="text" name="to" value='<?= $this->lang->line('label_to') ?>' class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Date sent</label>
                                                            <input type="text" name="date_sent" value='<?= $this->lang->line('label_date_sent') ?>' class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-whitesmoke text-md-right">
                                                    <button class="btn btn-primary" id="languages-save-btn">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php $data = get_system_settings('email');
                                    $dataemail = json_decode($data[0]['data']);
                                    ?>
                                    <div class="tab-pane fade" id="email-settings" role="tabpanel" aria-labelledby="email-tab4">
                                        <form action="<?= base_url('settings/save_settings'); ?>" id="email-setting-form">
                                            <div class="card" id="email-settings-card">
                                                <div class="card-header">
                                                    <h4>Email Settings</h4>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted">Email SMTP settings, notifications and others related to email.</p>
                                                    <div class="form-group row align-items-center">
                                                        <label for="email" class="form-control-label col-sm-3 text-md-right">Email</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="hidden" name="setting_type" class="form-control" value="email">
                                                            <input type="text" name="email" class="form-control" id="email" value="<?= !empty($dataemail->email) ? $dataemail->email : '' ?>">
                                                            <div class="form-text text-muted">This is the email address that the contact and report emails will be sent to, aswell as being the from address in signup and notification emails.</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row align-items-center">
                                                        <label for="password" class="form-control-label col-sm-3 text-md-right">Password</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="text" name="password" class="form-control" id="password" value="<?= !empty($dataemail->password) ? $dataemail->password : '' ?>">
                                                            <div class="form-text text-muted">Password of above given email.</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row align-items-center">
                                                        <label for="smtp_host" class="form-control-label col-sm-3 text-md-right">SMTP Host</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?= !empty($dataemail->smtp_host) ? $dataemail->smtp_host : '' ?>">
                                                            <div class="form-text text-muted">This is the host address for your smtp server, this is only needed if you are using SMTP as the Email Send Type.</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row align-items-center">
                                                        <label for="smtp_port" class="form-control-label col-sm-3 text-md-right">SMTP Hort</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?= !empty($dataemail->smtp_port) ? $dataemail->smtp_port : '' ?>">
                                                            <div class="form-text text-muted">SMTP port this will provide your service provider.</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="form-control-label col-sm-3 mt-3 text-md-right">E-mail Content Type</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <select class="form-control" name="mail_content_type" id="mail_content_type">
                                                                <?php
                                                                if (!empty($dataemail->mail_content_type)) {

                                                                    if ($dataemail->mail_content_type == 'text') { ?>
                                                                        <option value="text" selected>Text</option>
                                                                        <option value="html">HTML</option>
                                                                    <?php } else { ?>
                                                                        <option value="text">Text</option>
                                                                        <option value="html" selected>HTML</option>
                                                                    <?php }
                                                                } else { ?>
                                                                    <option value="text" selected>Text</option>
                                                                    <option value="html">HTML</option>
                                                                <?php } ?>
                                                            </select>
                                                            <div class="form-text text-muted">Text-plain or HTML content chooser.</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="form-control-label col-sm-3 mt-3 text-md-right">SMTP Encryption</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <select class="form-control" name="smtp_encryption" id="smtp_encryption">
                                                                <?php
                                                                if (!empty($dataemail->smtp_encryption)) {

                                                                    if ($dataemail->smtp_encryption == 'ssl') { ?>
                                                                        <option value="off">off</option>
                                                                        <option value="ssl" selected>SSL</option>
                                                                        <option value="tls">TLS</option>
                                                                    <?php } elseif ($dataemail->smtp_encryption == 'tls') { ?>
                                                                        <option value="off">off</option>
                                                                        <option value="ssl">SSL</option>
                                                                        <option value="tls" selected>TLS</option>
                                                                    <?php } else {  ?>
                                                                        <option value="off" selected>off</option>
                                                                        <option value="ssl">SSL</option>
                                                                        <option value="tls">TLS</option>
                                                                    <?php   }
                                                                } else { ?>
                                                                    <option value="off" selected>off</option>
                                                                    <option value="ssl">SSL</option>
                                                                    <option value="tls">TLS</option>
                                                                <?php } ?>
                                                            </select>
                                                            <div class="form-text text-muted">If your e-mail service provider supported secure connections, you can choose security method on list.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-whitesmoke text-md-right">
                                                    <button class="btn btn-primary" id="eamil-save-btn">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php $data = get_system_settings('web_fcm_settings');
                                    $datasystem = json_decode($data[0]['data']);
                                    ?>
                                    <div class="tab-pane fade" id="system-settings" role="tabpanel" aria-labelledby="system-tab4">
                                        <form action="<?= base_url('settings/save_settings'); ?>" id="system-setting-form">
                                            <div class="card" id="system-settings-card">
                                                <div class="card-header">
                                                    <h4>System Settings</h4>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted">FCM and other important settings.</p>
                                                    <div class="form-group row align-items-center">
                                                        <label for="apiKey" class="form-control-label col-sm-3 text-md-right">Web API Key</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="hidden" name="setting_type" class="form-control" value="system">
                                                            <input type="text" name="apiKey" class="form-control" id="apiKey" value="<?= !empty($datasystem->apiKey) ? $datasystem->apiKey : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row align-items-center">
                                                        <label for="projectId" class="form-control-label col-sm-3 text-md-right">Project ID</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="text" name="projectId" class="form-control" id="projectId" value="<?= !empty($datasystem->projectId) ? $datasystem->projectId : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row align-items-center">
                                                        <label for="messagingSenderId" class="form-control-label col-sm-3 text-md-right">Sender ID</label>
                                                        <div class="col-sm-6 col-md-9">
                                                            <input type="text" name="messagingSenderId" class="form-control" id="messagingSenderId" value="<?= !empty($datasystem->messagingSenderId) ? $datasystem->messagingSenderId : '' ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-whitesmoke text-md-right">
                                                    <button class="btn btn-primary" id="system-save-btn">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?= form_open('languages/create', 'id="modal-add-language-part"', 'class="modal-part"'); ?>
            <div class="form-group">
                <label>Langugae Name</label>
                <div class="input-group">
                    <?= form_input(['name' => 'language', 'placeholder' => 'For Ex: English', 'class' => 'form-control']) ?>
                </div>
            </div>
            <div class="form-group">
                <label>Langugae Code</label>
                <div class="input-group">
                    <?= form_input(['name' => 'code', 'placeholder' => 'For Ex: en', 'class' => 'form-control']) ?>
                </div>
            </div>
            <div class="form-check">
                <input class="form-check-input" name="is_rtl" type="checkbox" id="defaultCheck1">
                <label class="form-check-label" for="defaultCheck1">
                    Enable RTL
                </label>
            </div>
            </form>
            <?php include('include-footer.php'); ?>
        </div>
    </div>
    <?php include('include-js.php'); ?>
    <!-- Page Specific JS File -->
    <script src="<?= base_url('assets/js/page/features-setting-detail.js'); ?>"></script>
</body>

</html>