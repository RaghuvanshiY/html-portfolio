<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Calendar &mdash; <?= get_compnay_title(); ?></title>
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
                        <h1><?= !empty($this->lang->line('label_calendar')) ? $this->lang->line('label_calendar') : 'Calendar'; ?></h1>
                        <div class="section-header-breadcrumb">
                            <div class="btn-group mr-2 no-shadow">
                                <a href="<?= base_url('calendar') ?>" class="btn"><i class="fas fa-th-large"></i> <?= !empty($this->lang->line('label_grid_view')) ? $this->lang->line('label_grid_view') : 'Grid View'; ?></a>
                            </div>
                            <i class="btn btn-primary btn-rounded no-shadow" id="modal-add-event"><?= !empty($this->lang->line('label_create_event')) ? $this->lang->line('label_create_event') : 'Create Event'; ?></i>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <!-- <label>Default Select</label> -->
                                                <select id="type" name="type" class="form-control">
                                                    <option value="">All</option>
                                                    <option value="upcoming">Upcoming</option>

                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <i class="btn btn-primary btn-rounded no-shadow" id="filter-activity"><?= !empty($this->lang->line('label_filter')) ? $this->lang->line('label_filter') : 'Filter'; ?></i>
                                            </div>
                                        </div>

                                        <table class='table-striped' id='events_list' data-toggle="table" data-url="<?= base_url('calendar/get_events_list') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                                            "fileName": "events-list",
                                            "ignoreColumn": ["state"] 
                                            }' data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-sortable="true" data-visible="false"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'ID'; ?></th>
                                                    <th data-field="user_id" data-sortable="true" data-visible="false"><?= !empty($this->lang->line('label_user_id')) ? $this->lang->line('label_user_id') : 'User ID'; ?></th>
                                                    <th data-field="user_name" data-sortable="true" data-visible="false"><?= !empty($this->lang->line('label_user_name')) ? $this->lang->line('label_user_name') : 'User Name'; ?></th>
                                                    <th data-field="title" data-sortable="true"><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></th>
                                                    <th data-field="from_date" data-sortable="true"><?= !empty($this->lang->line('label_from_date')) ? $this->lang->line('label_from_date') : 'From Date'; ?></th>
                                                    <th data-field="to_date" data-sortable="true"><?= !empty($this->lang->line('label_to_date')) ? $this->lang->line('label_to_date') : 'To Date'; ?></th>
                                                    <th data-field="is_public" data-sortable="true"><?= !empty($this->lang->line('label_is_public')) ? $this->lang->line('label_is_public') : 'Public'; ?></th>
                                                    <th data-field="date_created" data-sortable="true" data-visible="false"><?= !empty($this->lang->line('label_date_created')) ? $this->lang->line('label_date_created') : 'Date Created'; ?></th>
                                                    <th data-field="action" data-sortable="false"><?= !empty($this->lang->line('label_action')) ? $this->lang->line('label_action') : 'Action'; ?></th>
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
            <?= form_open('calendar/create', 'id="modal-add-event-part"', 'class="modal-part"'); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                        <div class="input-group">
                            <?= form_input(['name' => 'title', 'placeholder' => 'Title', 'class' => 'form-control']) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                        <input class="form-control datetimepicker" type="text" id="start_date" name="start_date" value="" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                        <input class="form-control datetimepicker" type="text" id="end_date" name="end_date" value="" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= !empty($this->lang->line('label_background_color')) ? $this->lang->line('label_background_color') : 'Background Color'; ?></label>
                        <input type="color" name="background_color" class="form-control" value="#3f0df8">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= !empty($this->lang->line('label_text_color')) ? $this->lang->line('label_text_color') : 'Text Color'; ?></label>
                        <input type="color" name="text_color" class="form-control" value="#ffffff">
                    </div>
                </div>

                <div class="col-md-6">

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="is_public" class="custom-control-input" id="customCheck1">
                        <label class="custom-control-label" for="customCheck1"><?= !empty($this->lang->line('label_is_public')) ? $this->lang->line('label_is_public') : 'Is Public'; ?></label>
                    </div>

                </div>
            </div>
            </form>
            <div class="modal-edit-event"></div>
            <form action="<?= base_url('calendar/edit'); ?>" method="post" class="modal-part" id="modal-edit-event-part">
                <div class="row">
                    <div class="col-md-12">
                        <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                        <div class="input-group">
                            <input type="hidden" name="update_id" id="update_event_id">
                            <input type="text" class="form-control" placeholder="title" name="title" id="update_event_title">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                            <input class="form-control datetimepicker" type="text" id="update_event_start_date" name="start_date" autocomplete="off">
                            <input type="hidden" id="update_event_start_date_1" name="start_date_1">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                            <input class="form-control datetimepicker" type="text" id="update_event_end_date" name="end_date" autocomplete="off">
                            <input type="hidden" id="update_event_end_date_1" name="end_date_1">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_background_color')) ? $this->lang->line('label_background_color') : 'Background Color'; ?></label>
                            <input type="color" id="update_background_color" name="background_color" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_text_color')) ? $this->lang->line('label_text_color') : 'Text Color'; ?></label>
                            <input type="color" id="update_text_color" name="text_color" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_public" class="custom-control-input" id="customCheck2">
                            <label class="custom-control-label" for="customCheck2"><?= !empty($this->lang->line('label_is_public')) ? $this->lang->line('label_is_public') : 'Is Public'; ?></label>
                        </div>

                    </div>
                </div>
            </form>

            <?php include('include-footer.php'); ?>

        </div>
    </div>
    <?php include('include-js.php'); ?>
    <!-- Page Specific JS File -->
    <script>
        evnts = '';
    </script>
    <script src="<?= base_url('assets/js/page/components-calendar.js'); ?>"></script>
</body>

</html>