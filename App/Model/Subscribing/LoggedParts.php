<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Klapuch\Uri;

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

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		try {
			$this->origin->add($part, $uri, $expression);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function iterate(): \Iterator {
		try {
			return $this->origin->iterate();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}
