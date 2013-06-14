<?php
namespace lowtone\libre\src;
use lowtone\libre\src\out\LibreDocument,
	lowtone\util\documentable\interfaces\Documentable,
	lowtone\wp\hooks\Handler as HookHandler;

/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\themes\lowtone\libre\src
 */
class Libre extends HookHandler implements Documentable {

	/**
	 * The Libre document.
	 * @var LibreDocument
	 */
	protected $itsDocument;

	public function __priorities() {
		return array();
	}

	public function createDocument() {
		if (!($this->itsDocument instanceof LibreDocument))
			$this->itsDocument = new LibreDocument($this);

		return $this->itsDocument;
	}
	
}