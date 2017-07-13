<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Gajus\Dindent;
use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Model\Web;
use Texy;

final class FormattedSubscription implements Subscription {
	private $origin;
	private $texy;
	private $indenter;

	public function __construct(
		Subscription $origin,
		Texy\Texy $texy,
		Dindent\Indenter $indenter
	) {
		$this->origin = $origin;
		$this->texy = $texy;
		$this->indenter = $indenter;
	}

	public function cancel(): void {
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		$this->origin->edit($interval);
	}

	public function notify(): void {
		$this->origin->notify();
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('content', function(string $content): string {
				return (string) new Web\PrettyCode(
					$content,
					$this->texy,
					$this->indenter
				);
			})
			->adjusted('last_update', function(string $lastUpdate): string {
				return (new \DateTime($lastUpdate))->format('Y-m-d H:i');
			})
			->adjusted('interval', function(string $interval): string {
				return (string) new Time\TimeInterval(
					new \DateTimeImmutable(),
					new \DateInterval($interval)
				);
			})
			->adjusted('language', function(string $language): string {
				return (string) new Web\PrettyLanguage($language);
			});
	}
}