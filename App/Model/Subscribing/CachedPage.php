<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Nette\Caching;
use Remembrall\Model\Storage;

/**
 * Cache any given page
 */
final class CachedPage extends Storage\Cache implements Page {
	public function __construct(Page $origin, Caching\IStorage $cache) {
		parent::__construct($origin, $cache);
	}

	public function content(): \DOMDocument {
		return $this->read(__FUNCTION__);
	}

	public function url(): string {
		return $this->read(__FUNCTION__);
	}
}