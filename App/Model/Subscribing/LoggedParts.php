<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;

/**
 * Log every error action
 */
final class LoggedParts implements Parts {
	private $origin;
	private $logger;

	public function __construct(Parts $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		try {
			return $this->origin->subscribe($part, $interval);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function replace(Part $old, Part $new) {
		try {
			$this->origin->replace($old, $new);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function remove(Part $part) {
		try {
			$this->origin->remove($part);
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