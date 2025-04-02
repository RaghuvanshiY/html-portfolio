<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Home &mdash; <?= get_compnay_title(); ?></title>
    <?php include('include-css.php'); ?>
    <!-- /END GA -->
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            <?php include('include-header.php'); 
            $class = is_client()?'col-lg-4':'col-lg-3';
            ?>

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="row">
                        <div class="<?=$class?> col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-mid-dark">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4><?= !empty($this->lang->line('label_total_projects')) ? $this->lang->line('label_total_projects') : 'Total Projects'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <?= $total_project ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="<?=$class?> col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-mid-dark">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4><?= !empty($this->lang->line('label_total_tasks')) ? $this->lang->line('label_total_tasks') : 'Total Tasks'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <?= $total_task ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!is_client()) { ?>
                        <div class="<?=$class?> col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-mid-dark">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4><?= !empty($this->lang->line('label_users')) ? $this->lang->line('label_users') : 'Users'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <?= $total_user ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="<?=$class?> col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-mid-dark">
                                        <i class="fas fa-sticky-note"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4><?= !empty($this->lang->line('label_clients')) ? $this->lang->line('label_clients') : 'Clients'; ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <?= $total_client ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php } else { ?>
                            <div class="<?=$class?> col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-mid-dark">
                                        <i class="fas fa-sticky-note"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4><?= !empty($this->lang->line('label_notes')) ? $this->lang->line('label_notes') : 'Sticky Notes'; ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <?= $total_notes ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><?= !empty($this->lang->line('label_project_status')) ? $this->lang->line('label_project_status') : 'Project Status'; ?></h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="line-projects-chart"></canvas>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><?= !empty($this->lang->line('label_task_overview')) ? $this->lang->line('label_task_overview') : 'Tasks Overview'; ?></h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="line-tasks-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($this->session->has_userdata('workspace_id'))) { ?>
                            <div class='col-md-12'>
                                <div class="card">
                                    <div class="card-header">
                                        <h4><?= !empty($this->lang->line('label_task_insights')) ? $this->lang->line('label_task_insights') : 'Tasks Insights'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <!-- <label>Project Name</label> -->
                                                <input name="projects_name" id="projects_name" type="text" class="form-control" placeholder="<?= !empty($this->lang->line('label_project_name')) ? $this->lang->line('label_project_name') : 'Project Name'; ?>">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <!-- <label>Default Select</label> -->
                                                <select id="tasks_status" name="tasks_status" class="form-control">
                                                    <option value=""><?= !empty($this->lang->line('label_select_status')) ? $this->lang->line('label_select_status') : 'Select Status'; ?></option>
                                                    <option value="done"><?= !empty($this->lang->line('label_done')) ? $this->lang->line('label_done') : 'Done'; ?></option>
                                                    <option value="todo"><?= !empty($this->lang->line('label_todo')) ? $this->lang->line('label_todo') : 'Todo'; ?></option>
                                                    <option value="inprogress"><?= !empty($this->lang->line('label_in_progress')) ? $this->lang->line('label_in_progress') : 'In Progress'; ?></option>
                                                    <option value="review"><?= !empty($this->lang->line('label_review')) ? $this->lang->line('label_review') : 'Review'; ?></option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <!-- <label>Default Select</label> -->
                                                <input placeholder="<?= !empty($this->lang->line('label_tasks_due_dates_between')) ? $this->lang->line('label_tasks_due_dates_between') : 'Tasks Due Dates Between'; ?>" id="tasks_between" name="tasks_between" type="text" class="form-control" autocomplete="off">
                                                <input id="tasks_start_date" name="tasks_start_date" type="hidden">
                                                <input id="tasks_end_date" name="tasks_end_date" type="hidden">

                                            </div>
                                            <div class="form-group col-md-2">
                                                <i class="btn btn-primary btn-rounded no-shadow" id="fillter-tasks"><?= !empty($this->lang->line('label_filter')) ? $this->lang->line('label_filter') : 'Filter'; ?></i>
                                            </div>
                                        </div>
                                        <table class='table-striped' id='tasks_list' data-toggle="table" data-url="<?= base_url('home/get_tasks_list') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                      "fileName": "tasks-list",
                      "ignoreColumn": ["state"] 
                    }' data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'ID'; ?></th>
                                                    <th data-field="title" data-sortable="true"><?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?></th>
                                                    <th data-field="project_id" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'Project ID'; ?></th>
                                                    <th data-field="project_title" data-sortable="true"><?= !empty($this->lang->line('label_projects')) ? $this->lang->line('label_projects') : 'Project'; ?></th>

                                                    <th data-field="priority" data-sortable="true"><?= !empty($this->lang->line('label_priority')) ? $this->lang->line('label_priority') : 'Priority'; ?></th>
                                                    <th data-field="status" data-sortable="true"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></th>
                                                    <th data-field="due_date" data-sortable="true"><?= !empty($this->lang->line('label_due_date')) ? $this->lang->line('label_due_date') : 'Due Date'; ?></th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </div>

            <?php include('include-footer.php'); ?>

        </div>
    </div>

    <script>
        label_todo = "<?= !empty($this->lang->line('label_todo')) ? $this->lang->line('label_todo') : 'Todo'; ?>";
        label_in_progress = "<?= !empty($this->lang->line('label_in_progress')) ? $this->lang->line('label_in_progress') : 'In Progress'; ?>";
        label_review = "<?= !empty($this->lang->line('label_review')) ? $this->lang->line('label_review') : 'Review'; ?>";
        label_done = "<?= !empty($this->lang->line('label_done')) ? $this->lang->line('label_done') : 'Done'; ?>";
        label_tasks = "<?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?>";

        label_ongoing = "<?= !empty($this->lang->line('label_ongoing')) ? $this->lang->line('label_ongoing') : 'Ongoing'; ?>";
        label_finished = "<?= !empty($this->lang->line('label_finished')) ? $this->lang->line('label_finished') : 'Finished'; ?>";
        label_onhold = "<?= !empty($this->lang->line('label_onhold')) ? $this->lang->line('label_onhold') : 'OnHold'; ?>";

        label_projects = "<?= !empty($this->lang->line('label_projects')) ? $this->lang->line('label_projects') : 'Projects'; ?>";

        todo_task = "<?= $todo_task ?>";
        inprogress_task = "<?= $inprogress_task ?>";
        review_task = "<?= $review_task ?>";
        done_task = "<?= $done_task ?>";

        ongoing_project_count = "<?= $ongoing_project_count ?>";
        ongoing_project = "<?= $ongoing_project ?>";
        finished_project_count = "<?= $finished_project_count ?>";
        finished_project = "<?= $finished_project ?>";
        onhold_project_count = "<?= $onhold_project_count ?>";
        onhold_project = "<?= $onhold_project ?>";

        home_workspace_id = "<?= $this->session->userdata('workspace_id') ?>";
        home_user_id = "<?= $this->session->userdata('user_id') ?>";
        home_is_super_admin = "<?= $is_admin ?>";
    </script>

    <?php include('include-js.php'); ?>
    <script src="<?= base_url('assets/js/page/home.js'); ?>"></script>

</body>

</html>