<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Uri, Log
};

/**
 * Log every error action
 */
final class LoggedParts implements Parts {
	private $origin;
	private $logs;

	public function __construct(Parts $origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		try {
			$this->origin->add($part, $uri, $expression);
		} catch(\Throwable $ex) {
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::WARNING)
					)
				)
			);
			throw $ex;
		}
	}

	public function iterate(): iterable {
		try {
			return $this->origin->iterate();
		} catch(\Throwable $ex) {
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::WARNING)
					)
				)
			);
			throw $ex;
		}
	}
}
