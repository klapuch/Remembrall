<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;

final class DeleteForms {
	private $subscriptions;
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		array $subscriptions,
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		$this->subscriptions = $subscriptions;
		$this->url = $url;
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	public function render(): string {
		return array_reduce(
			$this->subscriptions,
			function(string $forms, Subscribing\Subscription $subscription): string {
				return $forms .= (new DeleteForm(
					$subscription,
					$this->url,
					$this->csrf,
					$this->storage
				))->render();
			},
			''
		);
	}
}