<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Uri, Log
};
use Remembrall\Model\Misc;

/**
 * Log every error action
 */
final class LoggedParts extends Misc\LoggingObject implements Parts {
	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->observe(__FUNCTION__, $part, $uri, $expression);
	}

	public function iterate(): \Iterator {
		return $this->observe(__FUNCTION__);
	}
}