<div class="alert alert-success" id="batch-post-alert-success" style="display: none">

</div>
<div class="alert alert-danger" id="batch-post-alert-error" style="display: none">

</div>
<div class="copier copier-site-info padding-top">
  <label class="block-display" for="site-address">Site</label>
  <input type="text" id="site-address" class="input-text text block-display full-width" placeholder="Target Site Address" value="<?php echo $site->domain; ?>"/>
</div>
<?php
for ($i = 0; $i < count($products); $i++)
{
$product = $products[$i];
?>
<div class="copier single-container">
  <h3>Item <?php echo ($i+1); ?></h3>
  <div class="copier copier-post-title">
    <label class="block-display" for="post-title-display">Post Title</label>
    <input type="text" id="post-title-display" class="input-text text block-display full-width" placeholder="Post Title" value="<?php echo $product->post_title; ?>"/>
  </div>

  <div class="copier copier-post-content">
    <label class="block-display" for="post-content-display">Post Content</label>
    <textarea id="post-content-display" class="input-text text block-display full-width" rows="6" placeholder="Post Content"><?php echo get_post_meta($product->ID, 'full_content', true); ?></textarea>
  </div>
</div>
<?php
}
?>
<div class="copier">
  <button id="submit-copy-content" data-ids="<?php echo implode(',', $productIds); ?>" class="button">Transfer Content</button>
</div>
