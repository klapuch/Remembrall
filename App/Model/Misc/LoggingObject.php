<?php
declare(strict_types = 1);
namespace Remembrall\Model\Misc;

use Klapuch\Log;

/**
 * Log every error action
 */
abstract class LoggingObject {
	private $origin;
	private $logs;

	final public function __construct($origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	/**
	 * Deal with the potential exception thrown by the called method with the given arguments
	 * @param string $method
	 * @param array ...$args
	 * @return mixed
	 */
	final protected function observe(string $method, ...$args) {
		try {
			return $this->origin->$method(...$args);
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