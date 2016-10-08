<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Storage;

/**
 * Cache any given part
 */
final class CachedPart extends Storage\Cache implements Part {
	public function content(): string {
		return $this->read(__FUNCTION__);
	}

	public function snapshot(): string {
		return $this->read(__FUNCTION__);
	}

	public function refresh(): Part {
		return $this->read(__FUNCTION__);
	}
}