<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Time
};
use Texy\Texy;

final class FormattedSubscription implements Subscription {
	private $origin;
	private $texy;

	public function __construct(Subscription $origin, Texy $texy) {
		$this->origin = $origin;
		$this->texy = $texy;
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
				return $this->texy->process(
					sprintf("/---code html \n %s", $content)
				);
			})
			->adjusted('last_update', function(string $lastUpdate): string {
				return (new \DateTime($lastUpdate))->format('Y-m-d H:i');
			});
	}
}