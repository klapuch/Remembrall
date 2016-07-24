<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Tracy;
use Remembrall\Model\Subscribing;

/**
 * Log every error action
 */
final class LoggedRequest implements Request {
	private $origin;
	private $logger;

	public function __construct(Request $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function send(): Subscribing\Page {
		try {
			return $this->origin->send();
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}