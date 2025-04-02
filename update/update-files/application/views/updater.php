<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Auto Update System &mdash; <?= get_compnay_title(); ?></title>

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
            <h1>Updater (Version: <?= $db_current_version ?>)</h1>
          </div>

          <div class="section-body">

            <div class="card">
              <div class="alert alert-danger">
                <div class="alert-title">NOTE:</div>
                Make sure you update system in sequence. Like if you have current version 1.0 and you want to update this version to 1.5 then you can't update it directly. You must have to update in sequence like first update version 1.2 then 1.3 and 1.4 so on.
              </div>
            </div>

              <div class="card">
                <?php if($is_updatable == true){ ?>
                <div class="card-header">
                  <h4>Update from <?= $db_current_version ?> to <?= $file_current_version ?></h4>
                </div>
                <?php }?>
                <div class="card-body">
                <?php if($is_updatable == true){ ?>This is an Automatic Updater helps you update your App From <?= $db_current_version ?> to <?= $file_current_version ?> <?php }?>
                <?php if($is_updatable == false){ ?><p>Drop the latest update.zip file here and after successful upload of the update.zip, click on Update Now button below to run the update.</p><?php }?>
                  <div class="form-group">
                    <div class="dropzone dz-clickable" id="update-file-dropzone">
                      <div class="dz-default dz-message">
                        <span>
                          <?= !empty($this->lang->line('label_drop_file_here_to_upload')) ? $this->lang->line('label_drop_file_here_to_upload') : 'Drop file here to upload'; ?>
                        </span>
                      </div>
                    </div>
                  </div>
                  <span id='success-msg' class="d-none">
                    <div class="alert alert-success">
                      <div class="alert-title">Successful</div>
                      System updated successfully. Click on reload button to see the magic.
                    </div>
                    <a href="<?= base_url(); ?>" class="btn btn-danger text-white">Reload Now</a>
                  </span>
                  <div id="notsuccess-msg" class="alert alert-danger d-none">
                   It seems your system is already upto date or something goes wrong. Please try again...
                  </div>
                </div>
                <div class="card-footer text-right">
                  <button id="updater-btn" class="btn btn-primary">Update Now</button>
                </div>
              </div>

          </div>
        </section>
      </div>

      <?php include('include-footer.php'); ?>

    </div>
  </div>

  <?php include('include-js.php'); ?>
</body>
<script>
    dictDefaultMessage = "<?= !empty($this->lang->line('label_drop_file_here_to_upload')) ? $this->lang->line('label_drop_file_here_to_upload') : 'Drop File Here To Upload'; ?>";
</script>
<script src="assets/js/page/components-updater.js"></script>
</html>