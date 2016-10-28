<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Time, Log
};

/**
 * Log every error action
 */
final class LoggedSubscription implements Subscription {
	private $origin;
	private $logs;

	public function __construct(Subscription $origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	public function cancel(): void {
		try {
			$this->origin->cancel();
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

	public function edit(Time\Interval $interval): void {
		try {
			$this->origin->edit($interval);
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

	public function notify(): void {
		try {
			$this->origin->notify();
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