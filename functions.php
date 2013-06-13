<?php
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */

namespace lowtone\libre {
	
	use lowtone\Util,
		lowtone\io\logging\Log,
		lowtone\content\packages\themes\Theme,
		lowtone\ui\forms\Input,
		lowtone\wp\WordPress,
		lowtone\wp\posts\Post,
		lowtone\wp\posts\meta\Meta,
		lowtone\wp\queries\Query;

	// Includes
	
	if (!include_once WP_PLUGIN_DIR . "/lowtone-content/lowtone-content.php") 
		return trigger_error("Lowtone Content plugin is required", E_USER_WARNING) && false;

	// Init
	
	$__i = Theme::init(array(
			Theme::INIT_PACKAGES => array("lowtone", "lowtone\\wp"),
			Theme::INIT_MERGED_PATH => __NAMESPACE__,
			Theme::INIT_SUCCESS => function() {

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
				
				// Fetch theme data
				
				$themeData = themeData();
				
				// Register menus
				
				$menus = array();
				
				foreach (array_filter($themeData["menus"]) as $menu) 
					$menus[strtolower(preg_replace("/\W+/", "_", $menu))] = __($menu);
				
				register_nav_menus($menus);
				
				// Register sidebars

				foreach (sidebarAttributes() as $id => $attributes) {
					if (($number = $themeData[$attributes[0]]) < 1)
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
				
				if ($themeData["thumbnail_size"])
					set_post_thumbnail_size($themeData["thumbnail_size"]["width"], $themeData["thumbnail_size"]["height"]);

				foreach ($themeData["image_sizes"] as $size) 
					add_image_size($size["name"], $size["width"], $size["height"], $size["crop"]);

				// Post formats
				
				if ($themeData["post_formats"])
					add_theme_support("post-formats", (array) $themeData["post_formats"]);

				// Add to head

				add_action("wp_head", function() {

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

				}, 0);
				
				// Generator
				
				add_filter("get_the_generator_xhtml", function($gen) {
					return sprintf('<meta name="generator" content="%s" />', implode(", ", apply_filters("generator", array("WordPress " . get_bloginfo( 'version' ), "Libre " . libreData("Version")))));
				});

				// Login
				
				add_action("login_head", function() {
					if (!($logo = Util::locateFile(imageFolders(), array("logo", "login"), ".png")))
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
				});

				// Replace attributes meta box
				
				add_action("add_meta_boxes", function($postType, $post) {
					if (post_type_supports($postType, "page-attributes")) {

						remove_meta_box("pageparentdiv", $postType, "side");

						add_meta_box("lowtone_libre_page_attributes", "page" == $postType ? __("Page Attributes") : __("Attributes"), function() use ($post) {
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
							
							if ("page" == $post->post_type && ($templates = templates())) {
								echo '<p><strong>' . __("Template") . '</strong></p>' . 
									'<label class="screen-reader-text" for="page_template">' . __("Page Template") . '</label>' . 
									'<select name="page_template" id="page_template">' . 
									'<option value="">' . __("Default Template") . '</option>';

								$selected = get_post_meta($post->ID, "_lowtone_libre_page_template", true);
								
								foreach ($templates as $id => $file) {
									$templateData = templateData($file);

									echo '<option value="' . esc_attr($basename = basename($file)) . '"' . ($basename == $selected ? ' selected="selected"' : "") . '>' . (isset($templateData["name"]) ? $templateData["name"] : $id) . '</option>';
								}

								echo '</select>';
									
							}
							
							echo '<p><strong>' . __("Order") . '</strong></p>' . 
								'<p><label class="screen-reader-text" for="menu_order">' . __("Order") . '</label><input name="menu_order" type="text" size="4" id="menu_order" value="' . esc_attr($post->menu_order) . '" /></p>';

							if ("page" == $post->post_type)
								echo '<p>' . __("Need help? Use the Help tab in the upper right of your screen.") . '</p>';
								
						}, $postType, "side", "core");

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
				}, 10, 2);

				// Save post

				add_action("save_post", function($postId, $post) {
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
				}, 10, 2);

				// Excerpt more
				
				add_filter("excerpt_more", function() {
					return '<span class="more">&hellip;</span>';
				});

				// Add class to excerpt
				
				add_filter("the_excerpt", function($excerpt) {
					return preg_replace("/<p>/i", '<p class="excerpt">', $excerpt);
				});

				// Admin
				
				if (!is_admin()) {
					
					// Register styles
					
					add_action("wp_print_styles", function() {
						wp_register_style(__NAMESPACE__, styles());
						wp_enqueue_style(__NAMESPACE__);
					});
					
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
				
				return true;
			},
		));
	
	if (!$__i)
		return false;
	
	// Functions
	
	/**
	 * Check if the current theme is the Libre theme.
	 * @return bool Returns TRUE if the current theme is Libre or FALSE if not.
	 */
	function isLibre() {
		return (basename(get_stylesheet_directory()) == "lowtone-libre");
	}
	
	/**
	 * Get file data including custom Libre data fields.
	 * @param string $file The path to the file to get the data from.
	 * @return array Returns the file data.
	 */
	function fileData($file) {
		$base = \lowtone\wp\themes\Theme::getData($file);
		
		$sidebarConfig = sidebarAttributes();
		
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
		
		return array_merge($base, $additional);
	}
	
	function extractFileData($file, $header = NULL) {
		$fileData = fileData($file);
		
		if (isset($header))
			return isset($fileData[$header]) ? $fileData[$header] : NULL;
			
		return $fileData;
	}
	
	/**
	 * Get the data for the current theme.
	 * @return array Returns the data array.
	 */
	function themeData($header = NULL) {
		return extractFileData(get_stylesheet_directory() . "/style.css", $header);
	}
	
	/**
	 * Get the data for the Libre theme.
	 * @return array Returns the data array.
	 */
	function libreData($header = NULL) {
		return extractFileData(get_template_directory() . "/style.css", $header);
	}
	
	/**
	 * Extract data from an XSL template.
	 * @param string $file The path for the XSL file.
	 * @return array Returns the data array.
	 */
	function templateData($file) {
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

		return $data;
	}

	/**
	 * Get the path for an XSL template for the current context.
	 * @return string|bool Returns the path for the template on success or FALSE 
	 * on failure.
	 */
	function template() {
		if ($pageTemplate = pageTemplate())
			return $pageTemplate;

		$templates = templates();

		$context = WordPress::context();
		
		$context[] = "index";

		/**
		 * Locate a template by breaking down its name. When a filename is 
		 * provided it is split on each dash (minus sign technically) and then 
		 * the function looks for a template removing the last part until a 
		 * matching template is found.
		 * @param string|array $name The name for the required template either 
		 * supplied as a dash separated string or an array of successive parts 
		 * of the name.
		 * @return string|bool Returns the path for the template on success or 
		 * FALSE on failure.
		 */
		$locate = function($name) use ($templates) {
			if (!is_array($name))
				$name = explode("-", $name);
			
			for ($parts = $name; $parts; array_pop($parts)) {
				if ($template = @$templates[implode("-", $parts)])
					return $template;
			}

			return false;
		};

		/**
		 * [$any description]
		 * @var [type]
		 */
		$any = function($find) use ($templates) {
			foreach ((array) $find as $t) {
				if ($template = @$templates[$t])
					return $template;
			}

			return false;
		};

		// Loop the context

		foreach ($context as $c) {
			switch ($c) {
				case Query::CONTEXT_TAX:
					$s = array("taxonomy");

					if ($term = get_queried_object()) {
						$s[] = $term->taxonomy;
						$s[] = $term->slug;
					}

					$template = $locate($s);
					
					break;

				case Query::CONTEXT_HOME:
					$template = @$any(array($templates[$c], $templates["index"]));
					
					break;

				case Query::CONTEXT_ATTACHMENT:
					global $posts;

					if (!($posts && ($mimeType = $posts[0]->post_mime_type)))
						break;

					if (!($type = explode("/", $mimeType)))
						break

					$template = @$any(array($templates[$type[0]], $templates[$type[1]], $templates[implode("_", $type)]));

					break;

				case Query::CONTEXT_SINGLE:
					$object = get_queried_object();

					$s = array($c);

					if ($object)
						$s[] = $object->post_type;

					$template = $locate($s);

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

					$s = array();

					if ($t && 0 === validate_file($t))
						$s[] = $t;

					if ($pagename)
						$s[] = "{$c}-{$pagename}";

					if ($id)
						$s[] = "{$c}-{$id}";

					$s[] = $c;

					$template = $any($s);

					break;

				case Query::CONTEXT_CATEGORY:
				case Query::CONTEXT_TAG:
					$object = get_queried_object();

					$s = array();

					if ($object) {
						$s[] = "{$c}-{$object->slug}";
						$s[] = "{$c}-{$object->term_id}";
					}

					$s[] = $c;

					$template = $any($s);
					
					break;

				case Query::CONTEXT_AUTHOR:
					$author = get_queried_object();

					$s = array();

					if ($author) {
						$s[] = "{$c}-{$author->user_nicename}";
						$s[] = "{$c}-{$author->ID}";
					}

					$s[] = $c;

					$template = $any($s);
					
					break;

				case Query::CONTEXT_ARCHIVE:
					$postTypes = get_query_var("post_type");

					$s = array();

					foreach ((array) $postTypes as $postType)
						$s[] = "{$c}-{$postType}";

					$s[] = $c;
					
					$template = $any($s);

					break;

				default: 
					$template = @$templates[$c];
			}

			if (!$template)
				continue;

			return $template;
		}

		return false;
	}

	function pageTemplate(array $template = NULL) {
		if (!is_page()) 
			return false;
		
		global $post;

		$template = get_post_meta($post->ID, "_lowtone_libre_page_template", true);

		if (!$template)
			return false;

		$templates = templates();
		
		$file = reset(array_filter($templates, function($file) use ($template) {
			return basename($file) == $template;
		}));

		if (!$file)
			return false;

		return $file;
	}

	function templates() {
		$templates = array();

		foreach (array(get_template_directory(), get_stylesheet_directory()) as $base) {
			foreach (glob($base . DIRECTORY_SEPARATOR . "templates/*.xsl") as $template) 
				$templates[basename($template, ".xsl")] = $template;
		}

		return $templates;
	}
	
	/**
	 * Get the URL for the stylesheet for the current context.
	 * @return string Returns a URL on success.
	 */
	function styles() {
		$styles = WordPress::context();
		
		foreach (array(get_stylesheet_directory(), get_template_directory()) as $base) {
			foreach ($styles as $style) {
				
				if (is_file($base . ($path = "/styles/" . $style . ".css")))
					return ($base == get_stylesheet_directory() ? get_stylesheet_directory_uri() : get_template_directory_uri()) . $path;
					
			}
		
		}
		
		return get_stylesheet_directory_uri() . "/style.css";
	}
	
	/**
	 * Get an array with config objects for the sidebars.
	 * @return array Returns the config for the sidebars
	 */
	function sidebarAttributes() {
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

	function imageFolders() {
		return array(
				get_stylesheet_directory() . "/images", 
				get_template_directory() . "/images"
			);
	}

	function filterName($name) {
		$name = "libre_{$name}";

		return $name;
	}

	// Plugin settings
	
	add_filter("rest_view_folders", function($folders, $pluginDir) {
		return array(
				get_stylesheet_directory() . "/views", 
				get_template_directory() . "/views", 
				$pluginDir . "/views"
			);
	}, 10, 2);
	
}