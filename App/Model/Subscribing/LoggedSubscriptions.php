<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Time, Uri, Log
};
use Remembrall\Model\Misc;

/**
 * Log every error action
 */
final class LoggedSubscriptions extends Misc\LoggingObject implements Subscriptions {
	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void {
		$this->observe(__FUNCTION__, $uri, $expression, $interval);
	}

	public function getIterator(): \Iterator {
		return $this->observe(__FUNCTION__);
	}

	public function print(Output\Format $format): array {
		return $this->observe(__FUNCTION__, $format);
	}
}