<?php

/**
  Plugin Name: Batch Product Uploader
  Plugin URI: https://www.upwork.com/freelancers/~01c35bdcf6cfdcdc7f
  Description: Batch upload textual posts as Woocommerce Products
  Version: 1.0.0
  Author: Md Ashfaq Salehin
  Author URI: https://www.upwork.com/freelancers/~01c35bdcf6cfdcdc7f
  License: MIT
**/

if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
  require_once 'includes/batch-product-uploader.php';
  register_activation_hook( __FILE__, array('BatchProductUploader', 'activatePlugin'));
  add_action('plugins_loaded', array( 'BatchProductUploader', 'getInstance' ));
}

?>
