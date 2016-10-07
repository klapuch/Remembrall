<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Klapuch\{
    Uri, Time, Output
};

/**
 * Log every error action
 */
final class LoggedSubscriptions implements Subscriptions {
	private $origin;
	private $logger;

	public function __construct(Subscriptions $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void {
		try {
			$this->origin->subscribe($uri, $expression, $interval);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function print(Output\Format $format): array {
		try {
			return $this->origin->print($format);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}