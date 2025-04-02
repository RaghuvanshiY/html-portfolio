<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Tasks &mdash; <?= get_compnay_title(); ?></title>
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
            <h1> <?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?></h1>
          </div>

          <div class="section-body">
            <div class="row">
              <div class="modal-edit-task "></div>
              <div class="modal-add-task-details"></div>




              <div class='col-md-12'>
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="form-group col-md-4">
                        <input name="projects_name" id="projects_name" type="text" class="form-control" placeholder="<?= !empty($this->lang->line('label_project_name')) ? $this->lang->line('label_project_name') : 'Project Name'; ?>">
                      </div>
                      <div class="form-group col-md-4">
                        <select id="tasks_status" name="tasks_status" class="form-control">
                          <option value=""><?= !empty($this->lang->line('label_select_status')) ? $this->lang->line('label_select_status') : 'Select Status'; ?></option>
                          <option value="done"><?= !empty($this->lang->line('label_done')) ? $this->lang->line('label_done') : 'Done'; ?></option>
                          <option value="todo"><?= !empty($this->lang->line('label_todo')) ? $this->lang->line('label_todo') : 'Todo'; ?></option>
                          <option value="inprogress"><?= !empty($this->lang->line('label_in_progress')) ? $this->lang->line('label_in_progress') : 'In Progress'; ?></option>
                          <option value="review"><?= !empty($this->lang->line('label_review')) ? $this->lang->line('label_review') : 'Review'; ?></option>
                        </select>
                      </div>
                      <div class="form-group col-md-4">
                        <input placeholder="<?= !empty($this->lang->line('label_tasks_due_dates_between')) ? $this->lang->line('label_tasks_due_dates_between') : 'Tasks Due Dates Between'; ?>" id="tasks_between" name="tasks_between" type="text" class="form-control" autocomplete="off">
                        <input id="tasks_start_date" name="tasks_start_date" type="hidden">
                        <input id="tasks_end_date" name="tasks_end_date" type="hidden">
                      </div>
                      <?php
                      if(!is_client()){
                    ?>
                      <div class="form-group col-md-3">
                        <select id="client_id" name="client_id" class="form-control">
                          <option value=""><?= !empty($this->lang->line('label_select_clients')) ? $this->lang->line('label_select_clients') : 'Select Clients'; ?></option>
                          <?php foreach ($all_user as $all_users) {
                            if (is_client($all_users->id)) { ?>
                              <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                          <?php }
                          } ?>
                        </select>
                      </div>
                      <div class="form-group col-md-3">
                        <select class="form-control" name="user_id" id="user_id">
                          <option value=""><?= !empty($this->lang->line('label_select_users')) ? $this->lang->line('label_select_users') : 'Select Users'; ?></option>
                          <?php foreach ($all_user as $all_users) {
                            if (!is_client($all_users->id)) { ?>
                              <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                          <?php }
                          } ?>
                        </select>
                      </div>
                      <?php }?>
                      <div class="form-group col-md-2">
                        <i class="btn btn-primary btn-rounded no-shadow" id="fillter-tasks">Filtter</i>
                      </div>
                    </div>
                    <table class='table-striped' id='tasks_list' data-toggle="table" data-url="<?= base_url('home/get_tasks_list') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                      "fileName": "tasks-list",
                      "ignoreColumn": ["state"] 
                    }' data-query-params="queryParams">
                      <thead>
                        <tr>
                          <th data-field="id" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'ID'; ?></th>
                          <th data-field="title" data-sortable="true"><?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?></th>
                          <th data-field="project_id" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'Project ID'; ?></th>
                          <th data-field="project_title" data-sortable="true"><?= !empty($this->lang->line('label_projects')) ? $this->lang->line('label_projects') : 'Project'; ?></th>
                          <th data-field="projects_userss" data-sortable="false"><?= !empty($this->lang->line('label_users')) ? $this->lang->line('label_users') : 'Users'; ?></th>
                          <th data-field="projects_clientss" data-sortable="false"><?= !empty($this->lang->line('label_clients')) ? $this->lang->line('label_clients') : 'Clients'; ?></th>
                          <th data-field="description" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_description')) ? $this->lang->line('label_description') : 'Description'; ?></th>
                          <th data-field="priority" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_priority')) ? $this->lang->line('label_priority') : 'Priority'; ?></th>
                          <th data-field="status" data-sortable="true"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></th>
                          <th data-field="due_date" data-visible='false' data-sortable="true"><?= !empty($this->lang->line('label_due_date')) ? $this->lang->line('label_due_date') : 'Due Date'; ?></th>
                          <th data-field="action" data-sortable="false">Action</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>




            </div>
          </div>
        </section>
      </div>

      <!--forms code goes here-->

      <?php include('include-footer.php'); ?>
    </div>
  </div>

  <?php include('include-js.php'); ?>
  <script src="<?= base_url('assets/js/page/tasks.js'); ?>"></script>

</body>

</html>