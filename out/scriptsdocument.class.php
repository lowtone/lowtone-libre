<?php
namespace lowtone\libre\out;
use lowtone\dom\Document;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */
class ScriptsDocument extends Document {
	
	public function build(array $options = NULL) {
		$this->updateBuildOptions((array) $options);
		
		$scriptsElement = $this->createAppendElement("scripts");
		
		return $this;
	}
	
}