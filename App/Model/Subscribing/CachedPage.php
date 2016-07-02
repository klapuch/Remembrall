<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Nette\Caching;

/**
 * Cache any given page
 */
final class CachedPage implements Page {
	private $origin;
	private $cache;

	public function __construct(Page $origin, Caching\IStorage $cache) {
		$this->origin = $origin;
		$this->cache = $cache;
	}

	public function content(): \DOMDocument {
		return $this->read(__FUNCTION__);
	}

	public function url(): string {
		return $this->read(__FUNCTION__);
	}

	private function read(string $method) {
		$key = __CLASS__ . '::' . $method;
		if($this->cache->read($key) === null)
			$this->cache->write($key, $this->origin->$method(), []);
		return $this->cache->read($key);
	}
}