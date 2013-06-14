<?php
namespace lowtone\libre\src\out;
use lowtone\Util,
	lowtone\dom\Document,
	lowtone\wp\menus\out\MenusDocument,
	lowtone\wp\queries\Query,
	lowtone\wp\queries\out\QueryDocument,
	lowtone\wp\sidebars\out\SidebarsDocument;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\themes\lowtone\libre\src\out
 */
class LibreDocument extends Document {
	
	const BUILD_INFO = "build_info",
		BUILD_STYLES = "build_styles",
		STYLES_DOCUMENT_OPTIONS = "styles_document_options",
		BUILD_SCRIPTS = "build_scripts",
		SCRIPTS_DOCUMENT_OPTIONS = "scripts_document_options",
		BUILD_QUERY = "build_query",
		QUERY_DOCUMENT_OPTIONS = "query_document_options",
		BUILD_MENUS = "build_menus",
		MENUS_DOCUMENT_OPTIONS = "menus_document_options",
		BUILD_SIDEBARS = "build_sidebars",
		SIDEBARS_DOCUMENT_OPTIONS = "sidebars_document_options";

	public function __construct($version = "1.0", $encoding = "utf-8") {
		parent::__construct($version, $encoding);

		$this->updateBuildOptions(array(
				self::BUILD_QUERY => true,
				self::BUILD_MENUS => true,
				self::BUILD_SIDEBARS => true,
			));
	}
	
	public function build(array $options = NULL) {
		$this->updateBuildOptions((array) $options);
		
		$libreElement = $this->createAppendElement("libre");
		
		$themeData = \lowtone\libre\libreData();
		
		$libreElement->setAttributes(array(
			"version" => $themeData["Version"]
		));
		
		// Output sent directly to the browser is collected as garbage (including notices and warnings but not errors)
		
		$libreDocument = $this;
		
		$garbage = Util::catchOutput(function() use ($libreDocument, $libreElement) {
			
			// WordPress head
			
			$libreElement->appendCreateElement("head", Util::catchOutput("wp_head"));

			// Settings
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_INFO)) {

				$info = array(
					// "home",
					// "siteurl",
					"url",
					"wpurl",
					"description",
					"rdf_url",
					"rss_url",
					"rss2_url",
					"atom_url",
					"comments_atom_url",
					"comments_rss2_url",
					"pingback_url",
					"stylesheet_url",
					"stylesheet_directory",
					"template_directory",
					"template_url",
					"admin_email",
					"charset",
					"html_type",
					"version",
					"language",
					"text_direction",
					"name"
				);

				$info = array_combine($info, $info);

				$info = array_map(function($show) {
					return get_bloginfo($show);
				}, $info);

				$info["body_class"] = implode(" ", get_body_class());
				
				$libreElement->appendCreateElement("info", $info);
			}
			
			// Styles
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_STYLES)) {
				
				$stylesDocument = new StylesDocument();
				
				$stylesDocument->build($libreDocument->getBuildOption(LibreDocument::STYLES_DOCUMENT_OPTIONS));
				
				if ($stylesElement = $libreDocument->importDocument($stylesDocument))
					$libreElement->appendChild($stylesElement);
					
			}
			
			// Scripts
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_SCRIPTS)) {
				
				$scriptsDocument = new ScriptsDocument();
				
				$scriptsDocument->build($libreDocument->getBuildOption(LibreDocument::SCRIPTS_DOCUMENT_OPTIONS));
				
				if ($scriptsElement = $libreDocument->importDocument($scriptsDocument))
					$libreElement->appendChild($scriptsElement);
				
			}
			
			// Query
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_QUERY)) {

				$queryDocument = new QueryDocument(new Query());
			
				$queryDocument->build($libreDocument->getBuildOption(LibreDocument::QUERY_DOCUMENT_OPTIONS));
				
				if ($queryElement = $libreDocument->importDocument($queryDocument))
					$libreElement->appendChild($queryElement);

			}
				
			// Menus
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_MENUS)) {

				$menusDocument = new MenusDocument();
			
				$menusDocument->build($libreDocument->getBuildOption(LibreDocument::MENUS_DOCUMENT_OPTIONS));
				
				if ($menusElement = $libreDocument->importDocument($menusDocument))
					$libreElement->appendChild($menusElement);

			}
				
			// Sidebars
			
			if ($libreDocument->getBuildOption(LibreDocument::BUILD_SIDEBARS)) {

				$sidebarsDocument = new SidebarsDocument();
				
				$sidebarsDocument->build($libreDocument->getBuildOption(LibreDocument::SIDEBARS_DOCUMENT_OPTIONS));
				
				if ($sidebarsElement = $libreDocument->importDocument($sidebarsDocument))
					$libreElement->appendChild($sidebarsElement);

			}

			// Build Libre document action

			do_action("build_" . \lowtone\libre\filterName("document"), $libreDocument);
			
			// WordPress footer
			
			$libreElement->appendCreateElement("footer", Util::catchOutput("wp_footer"));
			
		});
		
		if ($garbage) 
			$libreElement->appendCreateElement("garbage", $garbage);
			
		return $this;
	}
	
}