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

require_once 'includes/BatchProductUploader.php';
require_once 'includes/BatchProductUploaderSettings.php';
require_once 'includes/BatchProductUploaderContentCopy.php';
register_activation_hook( __FILE__, array('BatchProductUploader', 'activatePlugin'));
//register_deactivation_hook(__FILE__, array('BatchProductUploader', 'deactivatePlugin'));
add_action('plugins_loaded', array( 'BatchProductUploader', 'getInstance' ));
add_action('plugins_loaded', array( 'BatchProductUploaderSettings', 'getInstance' ));
add_action('plugins_loaded', array( 'BatchProductUploaderContentCopy', 'getInstance' ));

?>
