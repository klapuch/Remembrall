<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Klapuch\{
    Output, Time
};

/**
 * Log every error action
 */
final class LoggedSubscription implements Subscription {
	private $origin;
	private $logger;

	public function __construct(Subscription $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function cancel() {
		try {
			$this->origin->cancel();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function edit(Time\Interval $interval): Subscription {
		try {
			return $this->origin->edit($interval);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function print(Output\Format $format): Output\Format {
		try {
			return $this->origin->print($format);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}
