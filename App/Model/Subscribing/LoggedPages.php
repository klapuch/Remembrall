<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Log, Uri
};

/**
 * Log every error action
 */
final class LoggedPages implements Pages {
	private $origin;
	private $logs;

	public function __construct(Pages $origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	public function add(Uri\Uri $uri, Page $page): Page {
		try {
			return $this->origin->add($uri, $page);
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