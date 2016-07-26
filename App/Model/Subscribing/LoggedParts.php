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

	public function add(Part $part, string $url, string $expression): Part {
		try {
			return $this->origin->add($part, $url, $expression);
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