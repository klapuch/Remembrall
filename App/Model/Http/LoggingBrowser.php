<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Tracy;
use Remembrall\Model\Subscribing;

/**
 * Log every error action
 */
final class LoggingBrowser implements Browser {
	private $origin;
	private $logger;

	public function __construct(Browser $origin, Tracy\ILogger $logger) {
		$this->origin = $origin;
		$this->logger = $logger;
	}

	public function send(Request $request): Subscribing\Page {
		try {
			return $this->origin->send($request);
		} catch(\Throwable $ex) {
			$this->logger->log($ex, Tracy\Logger::ERROR);
			throw $ex;
		}
	}
}