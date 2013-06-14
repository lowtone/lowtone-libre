<?php
namespace lowtone\libre\src\out\scripts;
use lowtone\dom\Document;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\themes\lowtone\libre\src\out\scripts
 */
class ScriptsDocument extends Document {
	
	public function build(array $options = NULL) {
		$this->updateBuildOptions((array) $options);
		
		$scriptsElement = $this->createAppendElement("scripts");
		
		return $this;
	}
	
}