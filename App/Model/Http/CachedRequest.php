<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Nette\Caching;
use Remembrall\Model\{
	Storage, Subscribing
};

/**
 * Cache any given request
 */
final class CachedRequest extends Storage\Cache implements Request {
	public function __construct(Request $origin, Caching\IStorage $cache) {
		parent::__construct($origin, $cache);
	}

	public function send(): Subscribing\Page {
		return $this->read(__FUNCTION__);
	}
}