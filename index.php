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
		lowtone\wp\posts\out\PostListDocument,
		lowtone\wp\posts\out\PostDocument,
		lowtone\wp\queries\out\QueryDocument,
		lowtone\wp\sidebars\out\SidebarsDocument,
		lowtone\wp\sidebars\out\SidebarDocument,
		lowtone\wp\sidebars\widgets\out\WidgetsDocument,
		lowtone\wp\sidebars\widgets\out\WidgetDocument,
		lowtone\libre\out\LibreDocument;

	if (!class_exists("lowtone\\libre\\out\\LibreDocument"))
		return trigger_error("Libre theme not initiated", E_USER_ERROR);
	
	Util::call(function() {

		$log = Globals::get("log");
		
		// Create document

		$libreDocument = new LibreDocument();
		
		$sidebars = Util::getContext();
		
		$sidebars[] = "sidebar";

		if (is_singular())
			$sidebars[] = "post:" .  $GLOBALS["post"]->post_name;
		
		$options = array(
			LibreDocument::BUILD_INFO => true,
			LibreDocument::QUERY_DOCUMENT_OPTIONS => array(
				QueryDocument::BUILD_POSTS => true,
				QueryDocument::POST_LIST_DOCUMENT_OPTIONS => array(
					PostListDocument::BUILD_LOCALES => true,
					PostListDocument::POST_LIST_ELEMENT_NAME => "posts",
					PostListDocument::POST_DOCUMENT_OPTIONS => array(
						PostDocument::BUILD_TERMS => true,
						PostDocument::BUILD_AUTHOR => true,
						PostDocument::BUILD_CUSTOM_FIELDS => is_single(),
						PostDocument::BUILD_THUMBNAIL => true,
						PostDocument::BUILD_COMMENTS => is_single(),
						PostDocument::BUILD_COMMENT_FORM => is_single(),
						PostDocument::BUILD_ADJACENT => is_single()
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
		
		$options = apply_filters("lowtone_libre_document_options", $options);
		
		$libreDocument->build($options);
		
		if (is_super_admin() && isset($_GET["debug"])) 
			$libreDocument->out(array(Document::OPTION_CONTENT_TYPE => Document::CONTENT_TYPE_XML));
			
		if ($template = template()) {
			if (@constant("LOWTONE_LIBRE_APPEND_TEMPLATES") && ($appendTemplates = apply_filters("libre_append_templates", array(), $template))) {
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
			
		echo $libreDocument->saveHTML();

		$log->write("Libre success!");

		exit;
		
	});
	
}