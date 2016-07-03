<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;

/**
 * Log every error action
 */
final class LoggedReports implements Reports {
	private $origin;
	private $logger;

	public function __construct(Reports $origin, Tracy\Logger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function iterate(): array {
		try {
			return $this->origin->iterate();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function archive(Part $part) {
		try {
			$this->origin->archive($part);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}