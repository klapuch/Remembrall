<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;

/**
 * Log every error action
 */
final class LoggedPage implements Page {
	private $origin;
	private $logger;

	public function __construct(Page $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function content(): \DOMDocument {
		try {
			return $this->origin->content();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}

	public function refresh(): Page {
		try {
			return $this->origin->refresh();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}