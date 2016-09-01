<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Klapuch\Uri;

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
		Interval $interval
	) {
		try {
			$this->origin->subscribe($uri, $expression, $interval);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function iterate(): array {
		try {
			return $this->origin->iterate();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}