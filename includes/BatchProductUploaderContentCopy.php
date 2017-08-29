<?php

class BatchProductUploaderContentCopy
{
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

    add_action('wp_enqueue_scripts', array($this, 'frontEnqueueScripts'), 10);
    add_action('wp_ajax_submit_content_copy', array($this, 'submitContentCopy'));
    add_action('wp_ajax_nopriv_submit_content_copy', array($this, 'submitContentCopy'));

    add_action('woocommerce_thankyou', array($this, 'completedOrder'));

    add_shortcode('copy-content-multisite', array($this, 'copyMultisiteContentUi'));
  }

  public function frontEnqueueScripts()
  {
    $ajax_object = [
			'ajaxurl' => admin_url('admin-ajax.php')
		];
    wp_register_script($this->name.'-content-copier', $this->jsUrl.'contentCopier.js', array('jquery'));
		wp_enqueue_script($this->name.'-content-copier');
		wp_localize_script($this->name.'-content-copier', 'ajax_object', $ajax_object);
  }

  public function submitContentCopy()
  {
    $currentUser = wp_get_current_user();
    $primaryBlog = get_user_meta($currentUser->ID, 'primary_blog', true);
    $site = get_blog_details($primaryBlog);

    $ids = $_POST['ids'];
    $idArray = explode(',', $ids);
    foreach ($idArray as $id) {
      $post = get_post($id);
      $postTitle = $post->post_title;
      $postContent = get_post_meta($post->ID, 'full_content', true);
      $newPost = array(
        'post_title'    => $postTitle,
        'post_content'  => $postContent,
        'post_status'   => 'draft',
        'post_author'   => $currentUser->ID,
      );
      switch_to_blog($site->blog_id);
      wp_insert_post( $newPost );
      restore_current_blog();
    }
    echo json_encode(['status'=>'success']);
    wp_die();
  }

  public function copyMultisiteContentUi()
  {
    $currentUser = wp_get_current_user();
    $primaryBlog = get_user_meta($currentUser->ID, 'primary_blog', true);
    $site = get_blog_details($primaryBlog);
    $orderId = $_GET['order_id'];
    $order = wc_get_order($orderId);
    $items = $order->get_items();
    $products = [];
    $productIds = [];
    foreach ($items as $item)
    {
      $itemId = $item['product_id'];
      $products[] = get_post($itemId);
      $productIds[] = $itemId;
    }
    ob_start();
    include $this->templatesPath.'copy-content-multisite-ui.php';
    $code = ob_get_clean();
    return $code;
  }

  public function completedOrder($orderId)
	{
    ob_start();
    include $this->templatesPath.'content-copy-link.php';
    $code = ob_get_clean();
    echo $code;
	}
}
