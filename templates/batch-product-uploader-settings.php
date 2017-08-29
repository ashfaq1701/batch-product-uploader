<div class="container-fluid settings-top-container">
  <div class="alert alert-success" id="batch-post-settings-alert-success" style="display: none">

  </div>
  <div class="alert alert-danger" id="batch-post-settings-alert-error" style="display: none">

  </div>
  <h1>Batch Post Uploader Settings</h1>
  <div class="jumbotron">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label class="control-label" for="category">Select Category</label>
          <select class="form-control" id="category_select">
            <option value="0">Select Category</option>
            <?php
            foreach ($categories as $cat)
            {
            ?>
              <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
            <?php
            }
            ?>
            </select>
          </div>
        </div>
      </div>
  </div>
</div>
