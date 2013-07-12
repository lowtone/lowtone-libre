<?php
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */

namespace lowtone\libre {
	
	use lowtone\Util,
		lowtone\Globals,
		lowtone\dom\Document,
		lowtone\wp\WordPress,
		lowtone\wp\posts\collections\out\CollectionDocument,
		lowtone\wp\posts\out\PostDocument,
		lowtone\wp\queries\out\QueryDocument,
		lowtone\wp\sidebars\out\SidebarsDocument,
		lowtone\wp\sidebars\out\SidebarDocument,
		lowtone\wp\sidebars\widgets\out\WidgetsDocument,
		lowtone\wp\sidebars\widgets\out\WidgetDocument,
		lowtone\libre\src\out\LibreDocument;

	if (!class_exists("lowtone\\libre\\src\\Libre"))
		return trigger_error("Libre theme not initiated", E_USER_WARNING) && false;
	
	Util::call(function() {

		$libre = src\Libre::init();

		$log = Globals::get("log");
		
		// Create document

		$libreDocument = $libre->__toDocument();
		
		$sidebars = WordPress::context();
		
		$sidebars[] = "sidebar";

		if (is_singular())
			$sidebars[] = "post:" .  $GLOBALS["post"]->post_name;

		$sidebars = apply_filters(filterName("sidebars"), $sidebars);
		
		$options = array(
			LibreDocument::BUILD_INFO => true,
			LibreDocument::QUERY_DOCUMENT_OPTIONS => array(
				QueryDocument::BUILD_POSTS => true,
				QueryDocument::POST_COLLECTION_DOCUMENT_OPTIONS => array(
					CollectionDocument::BUILD_LOCALES => true,
					CollectionDocument::COLLECTION_ELEMENT_NAME => "posts",
					CollectionDocument::OBJECT_DOCUMENT_OPTIONS => array(
						PostDocument::BUILD_TERMS => true,
						PostDocument::BUILD_AUTHOR => true,
						PostDocument::BUILD_CUSTOM_FIELDS => is_singular(),
						PostDocument::BUILD_THUMBNAIL => true,
						PostDocument::BUILD_COMMENTS => is_singular(),
						PostDocument::BUILD_COMMENT_FORM => is_singular(),
						PostDocument::BUILD_ADJACENT => is_singular()
					),
				),
				QueryDocument::BUILD_PAGINATION => true
			),
			LibreDocument::SIDEBARS_DOCUMENT_OPTIONS => array(
				SidebarsDocument::FILTER_SIDEBARS => $sidebars,
				SidebarsDocument::SIDEBAR_DOCUMENT_OPTIONS => array(
					SidebarDocument::WIDGETS_DOCUMENT_OPTIONS => array(
						WidgetsDocument::WIDGET_DOCUMENT_OPTIONS => array(
							WidgetDocument::BUILD_OUTPUT => true
						)
					)
				)
			)
		);
		
		$options = apply_filters(filterName("document_options"), $options);
		
		$libreDocument->build($options);
		
		if (is_super_admin() && isset($_GET["debug"])) 
			$libreDocument->out(array(Document::OPTION_CONTENT_TYPE => Document::CONTENT_TYPE_XML));
			
		if ($template = apply_filters(filterName("template"), $libre->__template())) {
			if (src\Libre::appendTemplates() && ($appendTemplates = apply_filters(filterName("append_templates"), array(), $template))) {
				$cachedFile = dirname($template) . "/.cache/" . basename($template, ".xsl") . "-" . sha1(filemtime($template) . "|" . serialize($appendTemplates)) . ".xsl";

				if (!file_exists($cachedFile)) {
					$templateDocument = new Document(); // Create first, set properties, load later

					$templateDocument->preserveWhiteSpace = false;
					$templateDocument->formatOutput = true;

					$templateDocument->load($template);

					$templateDir = dirname($template);

					foreach ($appendTemplates as $template) {
						$templateDocument
							->documentElement
								->createAppendElement("xsl:include")
								->setAttribute("href", $template);
					}

					$templateDocument = Document::loadXml($templateDocument->saveXml());

					$refs = $templateDocument->getXPath()->query("//xsl:include|//xsl:import");

					foreach ($refs as $ref) {
						$href  = $ref->getAttribute("href");

						$absPath = realpath($href) ?: realpath($templateDir . DIRECTORY_SEPARATOR . $href);

						$ref->setAttribute("href", "file:///" . rawurlencode(str_replace("\\", "/", $absPath)));
					}

					if (!is_dir($cache = dirname($cachedFile)))
						mkdir($cache, 0777, true);

					$templateDocument->save($cachedFile);
				}

				$templateDocument = Document::load($cachedFile);
				
			} else
				$templateDocument = Document::load($template);

			$libreDocument = $libreDocument->transform($templateDocument);

		}

		/*if (!($libreDocument instanceof LibreDocument))
			return trigger_error("Failed loading the Libre document");*/
			
		echo apply_filters(filterName("ouput"), $libreDocument->saveHTML());

		$log->write("Libre success!");

		// exit;
		
	});
	
}