<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Storage;

/**
 * Cache any given page
 */
final class CachedPage extends Storage\Cache implements Page {
	public function content(): \DOMDocument {
		return $this->read(__FUNCTION__);
	}

	public function refresh(): Page {
		return $this->read(__FUNCTION__);
	}
}