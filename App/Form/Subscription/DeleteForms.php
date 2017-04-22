<?php
declare(strict_types = 1);
namespace Remembrall\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;

final class DeleteForms implements Form\Control {
	private $subscriptions;
	private $url;
	private $csrf;

	public function __construct(
		array $subscriptions,
		Uri\Uri $url,
		Csrf\Protection $csrf
	) {
		$this->subscriptions = $subscriptions;
		$this->url = $url;
		$this->csrf = $csrf;
	}

	public function validate(): void {
		// It is not needed
	}

	public function render(): string {
		return array_reduce(
			$this->subscriptions,
			function(string $forms, Subscribing\Subscription $subscription): string {
				return $forms .= (new DeleteForm(
					$subscription,
					$this->url,
					$this->csrf
				))->render();
			},
			''
		);
	}
}