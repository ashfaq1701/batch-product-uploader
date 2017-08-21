<?php

class BatchProductUploader
{
	private static $instance = null;
	private static $categories = [];

	public static function getInstance()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}

	public static function activatePlugin()
	{
		self::$categories = self::createCategories();
		self::addWoocommerceCategories();
	}

	public function __construct()
	{
		$this->name = 'batch-product-uploader';
		$this->pluginPath = ABSPATH . 'wp-content/plugins/batch-product-uploader/';
		$this->jsPath = $this->pluginPath.'assets/js/';
		$this->jsUrl = plugins_url($this->name.'/assets/js/');
		$this->cssUrl = plugins_url($this->name.'/assets/css/');
		$this->templatesPath = $this->pluginPath.'templates/';

		self::$categories = self::createCategories();

		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 10);
		add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'), 10);
		add_action('admin_menu', array($this, 'addAdminMenuPages'));

		add_action('wp_ajax_upload_posts_zip_file', array($this, 'uploadPostsZipFile'));
		add_action('wp_ajax_create_posts_batch', array($this, 'createPostsBatch'));
	}

	public static function createCategories() {
		return [
			'fb-post' => [
				'name' => 'Facebook Post',
				'description' => 'Post this item on your Facebook account',
				'slug' => 'fb-post'
			],
			'tweet' => [
				'name' => 'Tweet',
				'description' => 'Tweet this item on your Twitter account',
				'slug' => 'tweet'
			],
			'site-post' => [
				'name' => 'Site Post',
				'description' => 'Post this item on your Wordpress website',
				'slug' => 'site-post'
			]
		];
	}

	public function enqueueScripts()
	{
		$ajax_object = [
			'ajaxurl' => admin_url('admin-ajax.php')
		];
		wp_register_script($this->name.'-bootstrap-js', $this->jsUrl.'bootstrap.min.js', array('jquery'));
		wp_enqueue_script($this->name.'-bootstrap-js');
		wp_register_script($this->name.'-simpleUpload', $this->jsUrl.'simpleUpload.min.js', array('jquery'));
		wp_enqueue_script($this->name.'-simpleUpload');
		wp_register_script($this->name.'-ui', $this->jsUrl.'batchPostUploader.js', array('jquery', $this->name.'-simpleUpload'));
		wp_enqueue_script($this->name.'-ui');
		wp_localize_script($this->name.'-ui', 'ajax_object', $ajax_object);
	}

	public function enqueueStyles()
	{
		wp_enqueue_style($this->name.'-bootstrap-css', $this->cssUrl.'bootstrap.min.css');
		wp_enqueue_style($this->name.'-style', $this->cssUrl.'batchPostUploader.css');
	}

	public function addAdminMenuPages()
	{
		add_submenu_page('woocommerce', 'Batch Upload Text Products', 'Batch Upload', 'manage_options', 'batch-product-upload', array($this, 'batchProductUploadPage'));
	}

	public function batchProductUploadPage()
	{
		ob_start();
		include $this->templatesPath.'batch-product-uploader.php';
		$code = ob_get_clean();
		echo $code;
	}

	public static function addWoocommerceCategories()
	{
		foreach (self::$categories as $key => $item) {
			$term = wp_insert_term( $item['name'], 'product_cat', [
				'description'=> $item['description'],
				'slug' => $item['slug']
			]);
		}
	}

	public function uploadPostsZipFile()
	{
		$uploadDir = wp_upload_dir(date("Y/m"));
		$uploadPath = $uploadDir['path'];
		$targetFilePath = $uploadPath.'/'.'batchUpload-'.time().'.zip';
		move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath);
		$filetype = wp_check_filetype( basename( $targetFilePath ), null );
		$attachment = array(
			'guid'           => $uploadDir['url'] . '/' . basename( $targetFilePath ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $targetFilePath ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attachId = wp_insert_attachment( $attachment, $targetFilePath );
		echo json_encode(['id' => $attachId]);
		wp_die();
	}

	public function createPostsBatch()
	{
		$attachId = $_POST['attachId'];
		$category = $_POST['category'];
		$categoryName = self::$categories[$category]['name'];
		$attachedFile = get_attached_file($attachId);
		$pathInfo = pathinfo($attachedFile);
		$zip = new ZipArchive;
		$targetDir = '';
		if ($zip->open($attachedFile) === true) {
			$targetDir = $pathInfo['dirname'].'/'.$pathInfo['filename'];
			mkdir($targetDir);
			$zip->extractTo($targetDir);
			$zip->close();
			foreach(glob($targetDir.'/*.*') as $file) {
				$fileContent = file_get_contents($file);
				$this->createSinglePost($fileContent, $categoryName);
			}
			echo json_encode(['status' => 'Successfully imported all posts']);
		}
		else {
			echo json_encode(['status' => 'Could not extract archieve']);
		}
		wp_die();
	}

	public function createSinglePost($content, $category)
	{
		$currentUser = wp_get_current_user();
		$contentParts = explode('. ', $content);
		$firstLine = $contentParts[0];
		if(strlen($firstLine) > 60)
		{
			$postTitle = substr($firstLine, 0, 60);
		}
		else {
			$postTitle = $firstLine;
		}
		$post = array(
    	'post_author' => $currentUser->ID,
    	'post_content' => $content,
    	'post_status' => "publish",
    	'post_title' => $postTitle,
    	'post_parent' => '',
    	'post_type' => "product",
		);
		$postId = wp_insert_post( $post, $wp_error );
		if($postId) {
			wp_set_object_terms( $postId, $category, 'product_cat' );
		}
	}
}

?>
