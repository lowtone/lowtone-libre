<?php
namespace lowtone\libre\out;
use WP_Styles,
	lowtone\dom\Document;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */
class StylesDocument extends Document {
	
	/**
	 * @var WP_Styles
	 */
	protected $itsStyles;
	
	public function __construct() {
		parent::__construct();
		
		$this->itsStyles = $GLOBALS["wp_styles"];
		
	}
	
	public function build(array $options = NULL) {
		$this->updateBuildOptions((array) $options);
		
		$stylesElement = $this->createAppendElement("styles");
			
		foreach ((array) $this->itsStyles->queue as $queued) {
			if (!($dependency = @$this->itsStyles->registered[$queued]))
				continue;
		
			$src = $dependency->src;
		
			if (!preg_match("#^https?://#i", $src))
				$src = $this->itsStyles->base_url . $src;
		
			$src = add_query_arg('ver', $dependency->ver ?: $this->itsStyles->default_version, $src);
			
			$styleElement = $stylesElement
				->createAppendElement("style", array(
					"src" => trim($src),
					"media" => $dependency->args ?: "all"
				))
				->setAttributes(array(
					"handle" => $dependency->handle
				));
				
			if (@$dependency->extra["rtl"])
				$styleElement->setAttribute("rtl", "1");
			
		}
		
		return $this;
	}
	
}