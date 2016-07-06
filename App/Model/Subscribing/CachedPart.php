<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Nette\Caching;
use Remembrall\Model\Access;

/**
 * Cache any given part
 */
final class CachedPart implements Part {
	private $origin;
	private $cache;

	public function __construct(Part $origin, Caching\IStorage $cache) {
		$this->origin = $origin;
		$this->cache = $cache;
	}

	public function source(): Page {
		return $this->read(__FUNCTION__);
	}

	public function content(): string {
		return $this->read(__FUNCTION__);
	}

	public function equals(Part $part): bool {
		return $this->origin->equals($part);
	}

	public function expression(): Expression {
		return $this->read(__FUNCTION__);
	}

	public function visitedAt(): Interval {
		return $this->read(__FUNCTION__);
	}

	private function read(string $method) {
		$key = __CLASS__ . '::' . $method;
		if($this->cache->read($key) === null)
			$this->cache->write($key, $this->origin->$method(), []);
		return $this->cache->read($key);
	}
}