<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Log;

/**
 * Log every error action
 */
final class LoggedPage implements Page {
	private $origin;
	private $logs;

	public function __construct(Page $origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	public function content(): \DOMDocument {
		try {
			return $this->origin->content();
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

	public function refresh(): Page {
		try {
			return $this->origin->refresh();
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