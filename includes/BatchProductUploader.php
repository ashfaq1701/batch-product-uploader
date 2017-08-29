<?php

class BatchProductUploader
{
	private static $instance = null;
	private static $categories = [];
	private static $tags = [];

	public static function getInstance()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}

	public static function activatePlugin()
	{
		self::$categories = self::createCategories();
		self::$tags = self::createTags();
		self::addWoocommerceCategories();
		self::addWoocommerceTags();
		self::createPagesFly('Copy Content', '[copy-content-multisite]');
	}

	public static function deactivatePlugin()
	{
		self::$categories = self::createCategories();
		self::$tags = self::createTags();
		self::deleteWoocommerceCategories();
		self::deleteWoocommerceTags();
	}

	public static function createPagesFly($pageName, $content) {
    $createPage = array(
      'post_title'    => $pageName,
      'post_content'  => $content,
      'post_status'   => 'publish',
      'post_author'   => wp_get_current_user()->ID,
      'post_type'     => 'page',
      'post_name'     => $pageName
    );
    if(get_page_by_title($pageName) == NULL)
    {
      wp_insert_post( $createPage );
    }
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
		self::$tags = self::createTags();

		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 10);
		add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'), 10);
		add_action('wp_enqueue_scripts', array($this, 'frontEnqueueStyles'), 10);
		add_action('admin_menu', array($this, 'addAdminMenuPages'));

		add_action('wp_ajax_upload_posts_zip_file', array($this, 'uploadPostsZipFile'));
		add_action('wp_ajax_create_products_batch_woo', array($this, 'createPostsBatch'));
	}

	public function completedOrder($orderId)
	{
		echo "<p>Payment has been received for order $orderId</p>" ;
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

	public static function createTags()
	{
		return [
			'original' => [
				'name' => 'Original',
				'description' => 'Original Product',
				'slug' => 'original-product'
			],
			'non-original' => [
				'name' => 'Non Original',
				'description' => 'Non original product',
				'slug' => 'non-original-product'
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

	public function frontEnqueueStyles()
	{
		wp_enqueue_style($this->name.'-bootstrap-css', $this->cssUrl.'bootstrap.min.css');
		wp_enqueue_style($this->name.'-frontend-style', $this->cssUrl.'frontend.css');
	}

	public function addAdminMenuPages()
	{
		add_submenu_page('woocommerce', 'Batch Upload Text Products', 'Batch Upload', 'manage_options', 'batch-product-upload', array($this, 'batchProductUploadPage'));
	}

	public function batchProductUploadPage()
	{
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'product',
			'post_status' => 'publish'
		);
		$posts = get_posts( $args );
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

	public static function addWoocommerceTags()
	{
		foreach (self::$tags as $key => $item) {
			$term = wp_insert_term( $item['name'], 'product_tag', [
				'description'=> $item['description'],
				'slug' => $item['slug']
			]);
		}
	}

	public static function deleteWoocommerceCategories()
	{
		foreach (self::$categories as $key => $item) {
			$categories = get_terms(array(
	      'taxonomy' => 'product_cat',
				'name' => $item['name'],
	      'hide_empty' => 0,
	      'number' => 10
	    ));
			foreach($categories as $cat)
			{
				wp_delete_term( $cat->term_id, 'product_cat');
			}
		}
	}

	public static function deleteWoocommerceTags()
	{
		foreach (self::$tags as $key => $item) {
			$tags = get_terms(array(
	      'taxonomy' => 'product_tag',
				'name' => $item['name'],
	      'hide_empty' => 0,
	      'number' => 10
	    ));
			foreach($tags as $tag)
			{
				wp_delete_term( $tag->term_id, 'product_tag');
			}
		}
	}

	public function uploadPostsZipFile()
	{
		$uploadDir = wp_upload_dir(date("Y/m"));
		$uploadPath = $uploadDir['path'];
		$filename = explode('.', $_FILES["file"]["name"])[0];
		$targetFilePath = $uploadPath.'/'.$filename.'-'.time().'.zip';
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
		$type = $_POST['postType'];
		$categoryName = self::$categories[$category]['name'];
		$typeName = self::$tags[$type]['name'];
		$parent = $_POST['parent'];
		$attachedFile = get_attached_file($attachId);
		$pathInfo = pathinfo($attachedFile);
		$title = $_POST['title'];
		$customTitle = null;
		if($title == 'file-name')
		{
			$customTitleParts = explode('-', $pathInfo['filename']);
			unset($customTitleParts[count($customTitleParts) - 1]);
			$customTitle = implode('-', $customTitleParts);
		}
		else if($title == 'custom-title')
		{
			$customTitle = $_POST['customTitle'];
		}
		$zip = new ZipArchive;
		$targetDir = '';
		if ($zip->open($attachedFile) === true) {
			$targetDir = $pathInfo['dirname'].'/'.$pathInfo['filename'];
			if(!is_dir($targetDir))
			{
				mkdir($targetDir);
			}
			$zip->extractTo($targetDir);
			$zip->close();
			$counter = 0;
			foreach(glob($targetDir.'/*.*') as $file) {
				$counter++;
				$fileContent = file_get_contents($file);
				try
				{
						$this->createSinglePost($fileContent, $categoryName, $typeName, $title, $parent, $customTitle, $counter);
				}
				catch(Exception $e)
				{
					echo json_encode(['status' => $e->getMessage()]);
					wp_die();
				}
			}
			echo json_encode(['status' => 'Successfully imported all posts']);
		}
		else {
			echo json_encode(['status' => 'Could not extract archieve']);
		}
		wp_die();
	}

	public function formatContent($content, $every)
	{
		$chunkLen = floor(strlen($content) / $every);
		$chunkRem = strlen($content) % $every;
		$dotStr = '';
		for($i = 0; $i < $chunkLen; $i++)
		{
			$doStr = $doStr.'.';
		}
		$fmt = '';
		for($i = 0; $i < $every; $i++)
		{
			if($i % 2 == 0)
			{
				$chunk = substr($content, $i*$chunkLen, $chunkLen);
				$fmt = $fmt.$chunk;
			}
			else
			{
				$fmt = $fmt.' '.$doStr.' ';
			}
		}
		if($chunkRem != 0)
		{
			if($every % 2 == 0)
			{
				$chunk = substr($content, $every*$chunkLen, $chunkRem);
				$fmt = $fmt.$chunk;
			}
			else
			{
				$fmt = $fmt.' '.$doStr.' ';
			}
		}
		return $fmt;
	}

	public function createSinglePost($content, $category, $type, $title, $parent = 0, $customTitle = null, $counter = null)
	{
		$fmtContent = $this->formatContent($content, 10);
		$currentUser = wp_get_current_user();
		if($title == 'first-line')
		{
			$contentParts = explode(PHP_EOL, $content);
			$firstLine = $contentParts[0];
			if(strlen($firstLine) > 60)
			{
				$contentParts1 = explode('.', $firstLine);
				$firstLine1 = $contentParts1[0];
				if(strlen($firstLine1) > 60)
				{
					$postTitle = substr($firstLine1, 0, 60);
				}
				else
				{
					$postTitle = $firstLine1;
				}
			}
			else
			{
				$postTitle = $firstLine;
			}
		}
		else
		{
			$postTitle = $customTitle.'-'.$counter;
		}

		$price = 0;
		$categoryId = 0;
		$tagId = 0;

		$categories = get_terms(array(
      'taxonomy' => 'product_cat',
			'name' => $category,
      'hide_empty' => 0,
      'number' => 10
    ));

		if(count($categories) > 0)
		{
			$categoryObj = $categories[0];
			$categoryId = $categoryObj->term_id;
		}

		$tags= get_terms(array(
      'taxonomy' => 'product_tag',
			'name' => $type,
      'hide_empty' => 0,
      'number' => 10
    ));

		if(count($tags) > 0)
		{
			$tagObj = $tags[0];
			$tagId = $tagObj->term_id;
		}
		$priceSetting = null;

		if($categoryId != 0)
		{
			$args = array(
      	'post_type' => 'pricesetting',
      	'meta_key' => 'category_id',
      	'meta_value' => $categoryId,
      	'posts_per_page' => -1,
      	'post_status' => 'any'
    	);
    	$priceSettings = get_posts( $args );
			if(count($priceSettings) > 0)
			{
				$priceSetting = $priceSettings[0];
				$price = get_post_meta($priceSetting->ID, 'base_price', true);
			}
		}

		if($tagId != 0)
		{
			if(!empty($priceSetting))
			{
				$adjustmentsJSON = get_post_meta($priceSetting->ID, 'adjustments', true);
				$adjustmentsObj = json_decode($adjustmentsJSON, true);
				foreach ($adjustmentsObj as $k => $adj)
				{
					if($adj['tagId'] == $tagId)
					{
						if($price != 0)
						{
							$price = $price + $adj['tagAdj'];
						}
					}
				}
			}
		}

		$post = array(
    	'post_author' => $currentUser->ID,
    	'post_content' => $fmtContent,
    	'post_status' => "publish",
    	'post_title' => $postTitle,
    	'post_parent' => '',
    	'post_type' => "product",
			'meta_input' => array(
				'full_content' => $content
			)
		);
		if($parent != 0)
		{
			$post['post_parent'] = $parent;
		}

		if($price != 0)
		{
			$post['meta_input']['_virtual'] = 'yes';
			$post['meta_input']['_credits_amount'] = $price;
			$post['meta_input']['_price'] = $price;
		}

		if($type == 'Original')
		{
			$post['meta_input']['_stock'] = 1;
			$post['meta_input']['_manage_stock'] = 'yes';
		}
		$postId = wp_insert_post( $post, $wp_error );
		if($postId) {
			wp_set_object_terms( $postId, $category, 'product_cat' );
			wp_set_object_terms( $postId, $type, 'product_tag' );
		}
	}
}

?>
