<?php
declare(strict_types = 1);
namespace Remembrall\Model\Misc;

use Klapuch\Log;

/**
 * Callback with assurance that every potential error will be logged 
 */
final class LoggingCallback implements Callback {
	private $logs;

	public function __construct(Log\Logs $logs) {
		$this->logs = $logs;
	}

	public function invoke(callable $callback, array $args = []) {
		try {
			return call_user_func_array($callback, $args);
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