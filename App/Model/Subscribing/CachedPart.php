<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Nette\Caching;
use Remembrall\Model\Storage;
use Klapuch\Output;

/**
 * Cache any given part
 */
final class CachedPart extends Storage\Cache implements Part {
	public function __construct(Part $origin, Caching\IStorage $cache) {
		parent::__construct($origin, $cache);
	}

	public function content(): string {
		return $this->read(__FUNCTION__);
	}

	public function refresh(): Part {
		return $this->read(__FUNCTION__);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->read(__FUNCTION__, $format);
	}
}