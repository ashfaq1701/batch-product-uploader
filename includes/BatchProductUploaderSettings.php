<?php

class BatchProductUploaderSettings
{
	private $settings_api;
	public static $instance = null;

	public $pluginPath;
	public $assetPath;
	public $cssPath;

	public static function getInstance()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}

	function __construct() {
    $this->name = 'batch-product-uploader';
		$this->pluginPath = ABSPATH . 'wp-content/plugins/batch-product-uploader/';
		$this->jsPath = $this->pluginPath.'assets/js/';
		$this->jsUrl = plugins_url($this->name.'/assets/js/');
		$this->cssUrl = plugins_url($this->name.'/assets/css/');
		$this->templatesPath = $this->pluginPath.'templates/';

		add_action( 'admin_menu', array($this, 'adminMenu') );
    add_action( 'init', array($this, 'registerBatchProductUploaderPrice') );

    add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 10);

    add_action('wp_ajax_create_pricing_setting', array($this, 'createPricingSetting'));
    add_action('wp_ajax_get_pricing_setting', array($this, 'getPricingSetting'));
	}

  function enqueueScripts()
  {
    $ajax_object = [
			'ajaxurl' => admin_url('admin-ajax.php')
		];
    wp_register_script($this->name.'-settings-ui', $this->jsUrl.'batchPostUploaderSettings.js', array('jquery'));
		wp_enqueue_script($this->name.'-settings-ui');
		wp_localize_script($this->name.'-settings-ui', 'ajax_object', $ajax_object);
  }

	function adminMenu() {
		add_options_page( 'Batch Product Uploader Settings', 'Batch Product Uploader', 'delete_posts', 'batch-product-uploader-settings', array($this, 'settingsPage') );
	}

  function settingsPage()
  {
    $tags = get_terms(array(
      'taxonomy' => 'product_tag',
      'hide_empty' => 0,
      'number' => 10
    ));
    $categories = get_terms(array(
      'taxonomy' => 'product_cat',
      'hide_empty' => 0,
      'number' => 10
    ));
    ob_start();
    include $this->templatesPath.'batch-product-uploader-settings.php';
    $code = ob_get_clean();
    echo $code;
  }

  function registerBatchProductUploaderPrice()
  {
	  register_post_type( 'pricesetting', $args );
  }

  function createPricingSetting()
  {
    $pricingSetting = $_POST['setting'];
    $pricingSetting = str_replace("\\", '', $pricingSetting);
    $settingObj = json_decode($pricingSetting, true);
    $currentUser = wp_get_current_user();
		$catId = $settingObj['catId'];
		$args = array(
      'post_type' => 'pricesetting',
      'meta_key' => 'category_id',
      'meta_value' => $catId,
      'posts_per_page' => -1,
      'post_status' => 'any'
    );
    $posts = get_posts( $args );
    if(count($posts) == 0)
    {
			$post = array(
    		'post_author' => $currentUser->ID,
    		'post_content' => '',
    		'post_status' => "private",
    		'post_title' => 'Pricing Setting '.$catId,
    		'post_parent' => '',
    		'post_type' => 'pricesetting',
      	'meta_input' => array(
        	'category_id' => $catId,
        	'base_price' => $settingObj['basePrice'],
        	'adjustments' => json_encode($settingObj['adjustments'])
      	)
			);
    	$postId = wp_insert_post( $post, true );
		}
		else
		{
			$postId = $posts[0]->ID;
			update_post_meta($postId, 'category_id', $catId);
			update_post_meta($postId, 'base_price', $settingObj['basePrice']);
			update_post_meta($postId, 'adjustments', json_encode($settingObj['adjustments']));
		}
    wp_die();
  }

  function getPricingSetting()
  {
    $catId = $_GET['cat_id'];
    $args = array(
      'post_type' => 'pricesetting',
      'meta_key' => 'category_id',
      'meta_value' => $catId,
      'posts_per_page' => -1,
      'post_status' => 'any'
    );
    $posts = get_posts( $args );
    if(count($posts) > 0)
    {
      $post = $posts[0];
      $categoryId = get_post_meta($post->ID, 'category_id', true);
      $basePrice = get_post_meta($post->ID, 'base_price', true);
      $adjustments = json_decode(get_post_meta($post->ID, 'adjustments', true));
      $post->category_id = $categoryId;
      $post->base_price = $basePrice;
      $post->adjustments = $adjustments;
    }
    else {
      $post = null;
    }
    $tags = get_terms(array(
      'taxonomy' => 'product_tag',
      'hide_empty' => 0,
      'number' => 10
    ));
    $categories = get_terms(array(
      'taxonomy' => 'product_cat',
      'hide_empty' => 0,
      'number' => 10
    ));
    ob_start();
    include $this->templatesPath.'single-cat-price-setting-div.php';
    $code = ob_get_clean();
    echo $code;
    wp_die();
  }
}

?>
