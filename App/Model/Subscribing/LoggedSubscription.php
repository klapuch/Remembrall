<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Klapuch\Time;

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

	public function cancel(): void {
		try {
			$this->origin->cancel();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function edit(Time\Interval $interval): void {
		try {
			$this->origin->edit($interval);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
    }

    public function notify(): void {
		try {
			$this->origin->notify();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
    }
}
