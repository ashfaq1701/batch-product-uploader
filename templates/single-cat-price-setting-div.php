<div class="jumbotron pricing-top-container">
  <input type="hidden" id="category_id"/>
  <div class="row pricing-container">
    <div class="col-md-12">
      <label class="control-label">Base Price</label>
      <input class="form-control base-price" placeholder="Enter Base Price" value="<?php echo !empty($post) ? $post->base_price : ""; ?>"/>
    </div>
    <div class="col-md-12 adjustment-container padding-btm">
      <?php
      if(empty($post))
      {
      ?>
        <div class="row adjustment-container-item">
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Tag</label>
              <select class="form-control tag-select">
                <option value="0">Select Tag</option>
                <?php
                foreach ($tags as $tag)
                {
                ?>
                  <option value="<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></option>
                <?php
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Adjustment</label>
              <input type="text" class="form-control tag-adjustment"/>
            </div>
          </div>
        </div>
      <?php
      }
      else
      {
        foreach($post->adjustments as $adj)
        {
        ?>
          <div class="row adjustment-container-item">
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label">Tag</label>
                <select class="form-control tag-select">
                  <option value="0">Select Tag</option>
                  <?php foreach ($tags as $tag)
                  {
                  ?>
                    <option value="<?php echo $tag->term_id; ?>" <?php echo $tag->term_id == $adj->tagId ? 'selected' : ''; ?>><?php echo $tag->name; ?></option>
                  <?php
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label">Adjustment</label>
                <input type="text" class="form-control tag-adjustment" value="<?php echo $adj->tagAdj; ?>"/>
              </div>
            </div>
          </div>
        <?php
        }
      }
      ?>
      <button class="btn btn-default" id="add-adjustment" onclick="addNewAdjustment()">Add New Adjustment</button>
    </div>
    <div class="col-md-12">
      <div class="btn btn-success" id="save-pricing" onclick="savePricing()">Save Pricing</div>
    </div>
  </div>
</div>
