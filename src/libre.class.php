<?php
namespace lowtone\libre\src;
use lowtone\libre\src\out\LibreDocument,
	lowtone\types\singletons\interfaces\Singleton,
	lowtone\util\documentable\interfaces\Documentable,
	lowtone\wp\hooks\Handler as HookHandler,
	lowtone\Util,
	lowtone\io\logging\Log,
	lowtone\ui\forms\Input,
	lowtone\wp\WordPress,
	lowtone\wp\posts\Post,
	lowtone\wp\posts\meta\Meta,
	lowtone\wp\queries\Query;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\themes\lowtone\libre\src
 */
class Libre extends HookHandler implements Documentable, Singleton {

	/**
	 * Cached file data.
	 * @var array
	 */
	protected static $__fileData = array();

	/**
	 * Cached template data.
	 * @var array
	 */
	protected static $__templateData = array();

	/**
	 * A list of templates found.
	 * @var array
	 */
	protected static $__templates;

	/**
	 * A list of stylesheets found.
	 * @var array
	 */
	protected static $__stylesheets;

	/**
	 * The current theme context.
	 * @var array
	 */
	protected static $__context;

	/**
	 * Libre instances.
	 * @var array
	 */
	protected static $__instances = array();

	/**
	 * Constructor for the Libre class.
	 */
	protected function __construct() {

		// Set config defaults
		
		$defaults = apply_filters("lowtone_libre_config_defaults", array(
				"LOWTONE_LIBRE_APPEND_TEMPLATES" => true
			));

		foreach ($defaults as $constant => $value) {
			if (defined($constant))
				continue;

			define($constant, $value);
		}
		
		// Load language files
		
		foreach (array(get_stylesheet_directory(), get_template_directory()) as $base) {
			if (!is_dir($dir = $base . "/languages"))
				continue;
			
			load_theme_textdomain("lowtone_libre", $dir);
			
			break;
		}

		// Enable thumbnails
		 
		add_theme_support("post-thumbnails");

		// Default feed links
		
		add_theme_support("automatic-feed-links");
		
		// Register menus
		
		$menus = array();
		
		foreach (array_filter($this->__themeData("menus")) as $menu) 
			$menus[strtolower(preg_replace("/\W+/", "_", $menu))] = __($menu);
		
		register_nav_menus($menus);
		
		// Register sidebars

		foreach ($this->__sidebarAttributes() as $id => $attributes) {
			if (($number = $this->__themeData($attributes[0])) < 1)
				continue;
			
			register_sidebars($number, array(
				"name" => $attributes[2] . ($number > 1 ? " %d" : ""),
				"id" => $id,
				"description" => $attributes[3],
				"before_widget" => '<div id="%1$s" class="%2$s">', 
				"after_widget" => "</div>",
				"before_title" => "<header><h1>",
				"after_title" => "</h1></header>"
			));
		}

		$sidebarMeta = Meta::find(array(
				Meta::PROPERTY_META_KEY => "_lowtone_libre_sidebars"
			));

		$postIds = array_map(function($meta) {
			return $meta->{Meta::PROPERTY_POST_ID};
		}, (array) $sidebarMeta);

		$posts = Post::findById($postIds);

		$sidebarMeta->each(function($meta) use ($posts) {
			if (($number = $meta->{Meta::PROPERTY_META_VALUE}) < 1)
				return;

			$post = reset(array_filter((array) $posts, function($post) use ($meta) {
				return $post->{Post::PROPERTY_ID} == $meta->{Meta::PROPERTY_POST_ID};
			}));

			if (!$post)
				return;
			
			register_sidebars($number, array(
				"name" => __(get_post_type_object($post->{Post::PROPERTY_POST_TYPE})->labels->singular_name) . ": " . $post->{Post::PROPERTY_POST_TITLE} . ($number > 1 ? " %d" : ""),
				"id" => "post:" . $post->post_name(),
				"description" => sprintf(__('This sidebar is available when "%s" is displayed.', "lowtone_libre"), $post->{Post::PROPERTY_POST_TITLE}),
				"before_widget" => '<div id="%1$s" class="%2$s">', 
				"after_widget" => "</div>",
				"before_title" => "<header><h1>",
				"after_title" => "</h1></header>"
			));
		});
		
		// Image sizes
		
		if ($thumbnailSize = $this->__themeData("thumbnail_size"))
			set_post_thumbnail_size($thumbnailSize["width"], $thumbnailSize["height"]);

		foreach ($this->__themeData("image_sizes") as $size) 
			add_image_size($size["name"], $size["width"], $size["height"], $size["crop"]);

		// Post formats
		
		if ($postFormats = $this->__themeData("post_formats"))
			add_theme_support("post-formats", (array) $postFormats);

		if (!is_admin()) {
			
			// Add shortcodes
			
			foreach (array(get_stylesheet_directory(), get_template_directory()) as $base) {
				if (!is_dir($dir = $base . "/shortcodes"))
					continue;
				
				foreach (glob($dir . "/*.php") as $file) {
					add_shortcode(pathinfo($file, PATHINFO_FILENAME), function($atts, $content = null, $code = "") use ($file) {
						return Util::catchOutput(function() use ($atts, $content, $code, $file) {
							if (($result = include $file) && 1 !== $result) {

								if (is_callable($result))
									echo call_user_func_array($result, array($atts, $content, $code, $file));
								else 
									echo $result;

							}
						});
					});
				}
				
				break;
			}
			
		}

	}

	// Filters

	/**
	 * Add base, meta definitions, and title to the head.
	 */
	protected function wp_head() {

		// Base

		echo sprintf('<base href="%s" />', site_url() . "/");

		// Content-Type
		
		echo sprintf('<meta http-equiv="Content-Type" content="text/html; charset=%s" />', get_bloginfo("charset"));

		// Viewport
		
		$viewport = "initial-scale=1.0,maximum-scale=1.0";
		
		echo sprintf('<meta name="viewport" content="%s" />', $viewport);

		// Title

		$title = array(get_bloginfo("name"));

		if (is_single() || is_page())
			array_unshift($title, get_the_title());

		$title = (array) apply_filters("title", $title);
			
		echo '<title>' . implode(apply_filters("title_separator", " - "), $title) . '</title>';

	}

	/**
	 * Add Libre to the generator meta.
	 * @param string $gen The old generor tag.
	 * @return string Returns the new generator tag.
	 */
	protected function get_the_generator_xhtml($gen) {
		return sprintf('<meta name="generator" content="%s" />', implode(", ", apply_filters("generator", array("WordPress " . get_bloginfo( 'version' ), "Libre " . $this->__libreData("Version")))));
	}

	/**
	 * Replace logo on login page.
	 */
	protected function login_head() {
		if (!($logo = Util::locateFile($this->__imageFolders(), array("logo", "login"), ".png")))
			return;

		list($width, $height) = getimagesize($logo);

		$url = Util::pathToUrl($logo);

		if (!($url = apply_filters("login_logo", $url)))
			return;

		add_filter("login_headerurl", function() {
			return site_url();
		});

		add_filter("login_headertitle", function() {
			return get_bloginfo("name");
		});

		if ($width > 320) {
			$height = ($height * 320) / $width;
			$width = 320;
		}

		echo '<style>' .
			sprintf('body.login #login h1 a {background-image:url("%s");width:%spx;height:%spx;background-size:100%% auto;margin:0 auto;}', $url, $width, $height) . 
			'</style>';
	}

	/**
	 * Add page attributes post box and sidebars post box.
	 * @param string $postType The post type for the current post.
	 * @param WP_Post $post The current post object.
	 */
	protected function add_meta_boxes($postType, $post) {
		if (post_type_supports($postType, "page-attributes")) {

			remove_meta_box("pageparentdiv", $postType, "side");

			$libre = $this;

			add_meta_box(
				"lowtone_libre_page_attributes", 
				"page" == $postType ? __("Page Attributes") : __("Attributes"), 
				function() use ($post, $libre) {
					$postTypeObject = get_post_type_object($post->post_type);

					if ($postTypeObject->hierarchical) {
						$dropdownArgs = array(
							"post_type" => $post->post_type,
							"exclude_tree" => $post->ID,
							"selected" => $post->post_parent,
							"name" => "parent_id",
							"show_option_none" => __("(no parent)"),
							"sort_column" => "menu_order, post_title",
							"echo" => 0,
						);

						$dropdownArgs = apply_filters("page_attributes_dropdown_pages_args", $dropdownArgs, $post);
						
						$pages = wp_dropdown_pages($dropdownArgs);
						
						if (!empty($pages)) {
							echo '<p><strong>'. __('Parent') . '</strong></p>' . 
								'<label class="screen-reader-text" for="parent_id">'. __('Parent') . '</label>' . 
								$pages;
						}
								
					}
					
					if ("page" == $post->post_type && ($templates = $libre->__templates())) {
						echo '<p><strong>' . __("Template") . '</strong></p>' . 
							'<label class="screen-reader-text" for="page_template">' . __("Page Template") . '</label>' . 
							'<select name="page_template" id="page_template">' . 
							'<option value="">' . __("Default Template") . '</option>';

						$selected = get_post_meta($post->ID, "_lowtone_libre_page_template", true);
						
						foreach ($templates as $id => $file) {
							$templateData = $libre->__templateData($file);

							echo '<option value="' . esc_attr($basename = basename($file)) . '"' . ($basename == $selected ? ' selected="selected"' : "") . '>' . (isset($templateData["name"]) ? $templateData["name"] : $id) . '</option>';
						}

						echo '</select>';
							
					}
					
					echo '<p><strong>' . __("Order") . '</strong></p>' . 
						'<p><label class="screen-reader-text" for="menu_order">' . __("Order") . '</label><input name="menu_order" type="text" size="4" id="menu_order" value="' . esc_attr($post->menu_order) . '" /></p>';

					if ("page" == $post->post_type)
						echo '<p>' . __("Need help? Use the Help tab in the upper right of your screen.") . '</p>';
						
				}, 
				$postType, 
				"side", 
				"core"
			);

		}
		
		$form = new \lowtone\ui\forms\Form();

		foreach (array("post", "page") as $type) {
			add_meta_box(
				"sidebars",
				__("Sidebars", "lowtone_libre"),
				function($post) use ($form) {
					$post = new \lowtone\wp\posts\Post($post);
					
					$numSidebars = get_post_meta($post->getPostId(), "_lowtone_libre_sidebars", true);
						
					$form
						->createInput(Input::TYPE_TEXT, array(
							Input::PROPERTY_NAME => "sidebars",
							Input::PROPERTY_LABEL => __("Sidebars", "lowtone_libre"),
							Input::PROPERTY_VALUE => (int) $numSidebars
						))
						->out();
						
					//$hrOut();
					
					echo '<p class="lowtone comment">' . sprintf(__('Click <a href="%s">here</a> to configure the widgets.'), admin_url("widgets.php")) . '</p>';
						
				},
				$type,
				"side"
			);
		}
	}

	/**
	 * Update sidebars setting for pages.
	 * @param int $postId The subject post's ID.
	 * @param WP_Post $post The subject post.
	 */
	protected function save_post($postId, $post) {
		$postType = $post->{Post::PROPERTY_POST_TYPE};

		if ("revision" == $postType && ($parent = get_post($post->{Post::PROPERTY_POST_PARENT}))) 
			$postType = $parent->{Post::PROPERTY_POST_TYPE};

		if ("page" == $postType)
			update_post_meta($postId, "_lowtone_libre_page_template", @$_POST["page_template"]);

		if (Util::isAutosave())
			return;

		switch ($postType) {
			case "post":
			case "page":
			
				if (($sidebars = @$_POST["sidebars"]) > 0)
					update_post_meta($postId, "_lowtone_libre_sidebars", $sidebars);
				else
					delete_post_meta($postId, "_lowtone_libre_sidebars");
				
				break;
		}
	}

	/**
	 * Add stylesheet on front end.
	 */
	protected function wp_print_styles() {
		if (is_admin())
			return;

		wp_enqueue_style(basename(get_stylesheet_directory()) . "-style", get_stylesheet_directory_uri() . "/style.css");

		if ($stylesheet = $this->__stylesheet()) {
			$basename = basename($stylesheet, ".css");
			$dirname = basename(dirname($stylesheet));

			wp_enqueue_style($dirname . "-" . $basename, $stylesheet);
		}
	}

	/**
	 * Replace "More" string for excerpts.
	 * @return string Returns the new "More" string.
	 */
	protected function excerpt_more() {
		return '<span class="more">&hellip;</span>';
	}

	/**
	 * Add class definition to excerpt.
	 * @param string $excerpt The excerpt.
	 * @return string Returns the modified excerpt.
	 */
	protected function the_excerpt($excerpt) {
		return preg_replace("/<p>/i", '<p class="excerpt">', $excerpt);
	}

	// Support methods

	/**
	 * Get an array with config objects for the sidebars. Each entry has the 
	 * following values: key, meta name, name, descriptions.
	 * @return array Returns the config for the sidebars
	 */
	private function __sidebarAttributes() {
		return array(
			"404" => array("404_sidebars", "404 Sidebars", __("404", "lowtone_libre"), __("This sidebar is displayed when a page is not found.", "lowtone_libre")),
			"search" => array("search_sidebars", "Search Sidebars", __("Search", "lowtone_libre"), __("This sidebar is displayed with search results.", "lowtone_libre")),
			"tax" => array("tax_sidebars", "Tax Sidebars", __("Tax", "lowtone_libre"), __("This sidebar is displayed with taxonomies.", "lowtone_libre")),
			"home" => array("home_sidebars", "Home Sidebars", __("Home", "lowtone_libre"), __("This sidebar is visible on the main post index.", "lowtone_libre")),
			"front_page" => array("front_page_sidebars", "Front Page Sidebars", __("Front Page", "lowtone_libre"), __("This sidebar is only visible on the front page.", "lowtone_libre")),
			"attachment" => array("attachment_sidebars", "Attachment Sidebars", __("Attachment", "lowtone_libre"), __("This sidebar is displayed with attachments.", "lowtone_libre")),
			"single" => array("single_sidebars", "Single Sidebars", __("Single", "lowtone_libre"), __("This sidebar is visible when a single post is displayed (not with pages).", "lowtone_libre")),
			"page" => array("page_sidebars", "Page Sidebars", __("Page", "lowtone_libre"), __("This sidebar is visible when a page is displayed.", "lowtone_libre")),
			"category" => array("category_sidebars", "Category Sidebars", __("Category", "lowtone_libre"), __("This sidebar is displayed with categories.", "lowtone_libre")),
			"tag" => array("tag_sidebars", "Tag Sidebars", __("Tag", "lowtone_libre"), __("This sidebar is displayed with tags.", "lowtone_libre")),
			"author" => array("author_sidebars", "Author Sidebars", __("Author", "lowtone_libre"), __("This sidebar is displayed when an author is displayed.", "lowtone_libre")),
			"date" => array("date_sidebars", "Date Sidebars", __("Date", "lowtone_libre"), __("This sidebar is displayed a specific date is displayed.", "lowtone_libre")),
			"archive" => array("archive_sidebars", "Archive Sidebars", __("Archive", "lowtone_libre"), __("This sidebar is displayed when the archive is displayed.", "lowtone_libre")),
			"sidebar" => array("sidebars", "Sidebars", __("Sidebar", "lowtone_libre"), __("Default sidebar available on every page.", "lowtone_libre")),
		);
	}
	
	/**
	 * Get file data including custom Libre data fields.
	 * @param string $file The path to the file to get the data from.
	 * @return array Returns the file data.
	 */
	private function __extractFileData($file) {
		if (isset(self::$__fileData[$file]))
			return self::$__fileData[$file];

		$base = \lowtone\wp\themes\Theme::getData($file);
		
		$sidebarConfig = self::__sidebarAttributes();
		
		$sidebarKeys = array_map(function($config) {
				return $config[0];
			}, $sidebarConfig);
		
		$sidebarHeaders = array_map(function($config) {
				return $config[1];
			}, $sidebarConfig);
		
		$headers = array_merge(
			array_combine($sidebarKeys, $sidebarHeaders),
			array(
				"theme_support" => "Theme support",
				"menus" => "Menus",
				"thumbnail_size" => "Thumbnail size",
				"image_sizes" => "Image sizes",
				"post_formats" => "Post formats",
			)
		);
		
		$additional = get_file_data($file, $headers, "libre");
		
		$keys = array_keys($additional);

		$explode = function($value) {
			if (!trim($value))
				return NULL;

			return array_map("trim", str_getcsv($value));
		};

		$parseImageSizes = function($value) use ($explode) {
			return array_map(function($size) {
				@list($dimensions, $name, $crop) = array_reverse(array_map("trim", explode(":", $size)));

				@list($width, $height) = array_map("trim", explode("x", $dimensions));

				return array(
						"name" => $name ?: $dimensions,
						"width" => $width,
						"height" => $height,
						"crop" => "crop" == $crop
					);
			}, (array) $explode($value));
		};
		
		$additional = array_combine($keys, array_map(function($value, $key) use ($explode, $parseImageSizes) {
			switch ($key) {
				case "404_sidebars":
				case "search_sidebars":
				case "tax_sidebars":
				case "attachment_sidebars":
				case "tag_sidebars":
				case "author_sidebars":
				case "date_sidebars":
				case "archive_sidebars":
				case "front_page_sidebars":
					$value = is_numeric($value) ? (int) $value : 0;
					break;
					
				case "home_sidebars":
				case "page_sidebars":
				case "single_sidebars":
				case "category_sidebars":
					$value = is_numeric($value) ? (int) $value : 1;
					break;
					
				case "sidebars":
					$value = is_numeric($value) ? (int) $value : 4;
					break;

				case "theme_support":
					$value = $explode($value);
					break;
					
				case "menus":
					$value = $explode($value);
					break;

				case "thumbnail_size":
					$value = reset($parseImageSizes($value)) ?: NULL;
					break;

				case "image_sizes":
					$value = $parseImageSizes($value);
					break;

				case "post_formats":
					$value = $explode($value);
					break;
			}
			
			return $value;
		}, $additional, $keys));
		
		return (self::$__fileData[$file] = array_merge($base, $additional));
	}

	/**
	 * Get theme meta data from a file.
	 * @param string $file The path to the file.
	 * @param string|NULL $header An optional header to extract specific data.
	 * @return array|string Returns either all data or a single value if a valid
	 * header is specified.
	 */
	public function __fileData($file, $header = NULL) {
		$fileData = self::__extractFileData($file);
		
		if (isset($header))
			return isset($fileData[$header]) ? $fileData[$header] : NULL;
			
		return $fileData;
	}

	/**
	 * Get the data for the current theme.
	 * @return array Returns the data array.
	 */
	public function __themeData($header = NULL) {
		return self::__fileData(get_stylesheet_directory() . "/style.css", $header);
	}

	/**
	 * Get the data for the Libre theme.
	 * @return array Returns the data array.
	 */
	public function __libreData($header = NULL) {
		return self::__fileData(get_template_directory() . "/style.css", $header);
	}

	/**
	 * Extract data from an XSL template.
	 * @param string $file The path for the XSL file.
	 * @return array Returns the data array.
	 */
	public function __templateData($file) {
		if (isset(self::$__templateData[$file]))
			return self::$__templateData[$file];

		$data = array();

		if (!($document = \DOMDocument::load($file)))
			return $data;

		$xpath = new \DOMXPath($document);

		if (!($comment = $xpath->query("//comment()[1]")->item(0)))
			return $data;

		$tag = NULL;

		$tags = array();
		
		foreach (explode("\n", $comment->nodeValue) as $line) {
			$line = trim($line);

			if (preg_match("/^@([[:alpha:]-_]+)(.+)/i", $line, $matches)) {
				$tag = strtolower(trim($matches[1]));
				$line = trim($matches[2]);
			}

			if (!$tag)
				continue;

			$tags[$tag][] = $line;
		}

		$data = array_map(function($lines) {
			return implode(" ", array_filter($lines));
		}, $tags);

		return (self::$__templateData[$file] = $data);
	}

	/**
	 * Get a list of folders where the theme should look for images.
	 * @return array Returns a list of folders.
	 */
	public function __imageFolders() {
		return array(
				get_stylesheet_directory() . "/images", 
				get_template_directory() . "/images"
			);
	}

	/**
	 * Get the template context.
	 * @param boolean $cached Whether to return a cached context.
	 * @return array Returns the context.
	 */
	public function __context($cached = true) {
		if ($cached && isset(self::$__context))
			return self::$__context;

		$context = array();

		foreach (WordPress::context() as $c) {
			switch ($c) {
				case Query::CONTEXT_TAX:
					$context = "taxonomy";

					if ($term = get_queried_object()) {
						$context[] = "taxonomy-{$term->taxonomy}";
						$context[] = "taxonomy-{$term->taxonomy}-{$term->slug}";
					}
					
					break;

				case Query::CONTEXT_HOME:
					$context[] = $c;
					$context[] = "index";
					break;

				case Query::CONTEXT_ATTACHMENT:
					global $posts;

					if (!($posts && ($mimeType = $posts[0]->post_mime_type)))
						break;

					if (!($type = explode("/", $mimeType)))
						break

					$context[] = $type[0];
					$context[] = $type[1];
					$context[] = implode("_", $type);

					break;

				case Query::CONTEXT_SINGLE:
					$object = get_queried_object();

					$context[] = $c;

					if ($object)
						$context[] = "{$c}-{$object->post_type}";

					break;

				case Query::CONTEXT_PAGE:
					$id = get_queried_object_id();
					$t = get_page_template_slug();
					$pagename = get_query_var("pagename");

					if (!$pagename && $id) {
						// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
						$post = get_queried_object();
						$pagename = $post->post_name;
					}

					if ($t && 0 === validate_file($t))
						$context[] = $t;

					if ($pagename)
						$context[] = "{$c}-{$pagename}";

					if ($id)
						$context[] = "{$c}-{$id}";

					$context[] = $c;

					break;

				case Query::CONTEXT_CATEGORY:
				case Query::CONTEXT_TAG:
					$object = get_queried_object();

					if ($object) {
						$context[] = "{$c}-{$object->slug}";
						$context[] = "{$c}-{$object->term_id}";
					}

					$context[] = $c;
					
					break;

				case Query::CONTEXT_AUTHOR:
					$author = get_queried_object();

					if ($author) {
						$context[] = "{$c}-{$author->user_nicename}";
						$context[] = "{$c}-{$author->ID}";
					}

					$context[] = $c;
					
					break;

				case Query::CONTEXT_ARCHIVE:
					$postTypes = get_query_var("post_type") ?: array(Post::__postType());

					foreach ((array) $postTypes as $postType)
						$context[] = "{$c}-{$postType}";

					$context[] = $c;

					break;

				default: 
					$context[] = $c;
			}
		}

		return (self::$__context = $context);
	}

	/**
	 * Get a list of all available templates.
	 * @return array Returns a list of all available templates.
	 */
	public function __templates() {
		if (isset(self::$__templates))
			return self::$__templates;

		$templates = array();

		foreach (array(get_template_directory(), get_stylesheet_directory()) as $base) {
			foreach (glob($base . DIRECTORY_SEPARATOR . "templates/*.xsl") as $template) 
				$templates[basename($template, ".xsl")] = $template;
		}

		return (self::$__templates = $templates);
	}

	/**
	 * Get the template selection for the current page if available.
	 * @return string|bool Returns the template for the current page of FALSE 
	 * if no page is displayed or the current page has no template set.
	 */
	public function __pageTemplate() {
		if (!is_page()) 
			return false;
		
		global $post;

		$template = get_post_meta($post->ID, "_lowtone_libre_page_template", true);

		if (!$template)
			return false;

		foreach ($this->__templates() as $key => $file) {
			if (basename($template, ".xsl") != $key)
				continue;

			return $file;
		}

		return false;
	}

	/**
	 * Locate the template.
	 * @return string|bool Returns the path to the template or FALSE if no 
	 * template was found.
	 */
	public function __template() {
		if ($pageTemplate = $this->__pageTemplate())
			return $pageTemplate;

		$context = $this->__context();

		$context[] = "index";

		$templates = $this->__templates();

		foreach ($context as $c) {
			if (!isset($templates[$c]))
				continue;

			return $templates[$c];
		}

		return false;
	}

	/**
	 * Get a list of all available stylesheets.
	 * @return array Returns a list of available stylesheets.
	 */
	public function __stylesheets() {
		if (isset(self::$__stylesheets))
			return self::$__stylesheets;

		$stylesheets = array();

		foreach (array(get_template_directory(), get_stylesheet_directory()) as $i => $base) {
			$baseUri = 0 == $i ? get_template_directory_uri() : get_stylesheet_directory_uri();

			foreach (glob($base . "/*.css") as $stylesheet) 
				$stylesheets[basename($stylesheet, ".css")] = $baseUri . "/" . basename($stylesheet);
		}

		return (self::$__stylesheets = $stylesheets);
	}

	/**
	 * Get the stylesheet for the current context.
	 * @return string|bool Returns the URL for a stylesheet or FALSE if no 
	 * stylesheet was found.
	 */
	public function __stylesheet() {
		$stylesheets = $this->__stylesheets();

		foreach ($this->__context() as $c) {
			if (!isset($stylesheets[$c]))
				continue;

			return $stylesheets[$c];
		}

		return false;
	}

	// Hook handler

	public function __priorities() {
		return array(
				"wp_head" => 0,
			);
	}

	public function __maxAdds() {
		return 1;
	}

	// Documentable

	public function __toDocument() {
		return new out\LibreDocument();
	}

	// Static

	public static function __instance() {
		$class = get_called_class();

		if (!(isset($__instances[$class]) && self::$__instances[$class] instanceof $class)) 
			self::$__instances[$class] = new $class();

		return self::$__instances[$class];
	}

	public static function init() {
		return static::__instance()
			->__add();
	}
	
}