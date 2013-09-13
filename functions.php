<?php
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */

namespace lowtone\libre {
	
	use lowtone\content\packages\themes\Theme;

	// Includes
	
	if (!include_once WP_PLUGIN_DIR . "/lowtone-content/lowtone-content.php") 
		return trigger_error("Lowtone Content plugin is required", E_USER_WARNING) && false;

	// Init
	
	$__i = Theme::init(array(
			Theme::INIT_PACKAGES => array("lowtone", "lowtone\\wp"),
			Theme::INIT_MERGED_PATH => __NAMESPACE__,
			Theme::INIT_SUCCESS => function() {
				src\Libre::init();
				
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
	 * Get theme meta data from a file.
	 * @param string $file The path to the file.
	 * @param string|NULL $header An optional header to extract specific data.
	 * @return array|string Returns either all data or a single value if a valid
	 * header is specified.
	 */
	function fileData($file, $header = NULL) {
		return src\Libre::__fileData($file, $header);
	}
	
	/**
	 * Get the data for the current theme.
	 * @return array Returns the data array.
	 */
	function themeData($header = NULL) {
		return src\Libre::__themeData($header);
	}
	
	/**
	 * Get the data for the Libre theme.
	 * @return array Returns the data array.
	 */
	function libreData($header = NULL) {
		return src\Libre::__libreData($header);
	}
	
	/**
	 * Extract data from an XSL template.
	 * @param string $file The path for the XSL file.
	 * @return array Returns the data array.
	 */
	function templateData($file) {
		return src\Libre::__templateData($file);
	}

	function imageFolders() {
		return src\Libre::__imageFolders();
	}

	function filterName($name) {
		return "libre_{$name}";
	}
	
}