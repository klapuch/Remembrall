<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Control\Subscription;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class EditPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Subscription\EditForm(
					$this->subscription($parameters['id']),
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitEdit(array $subscription, array $parameters): void {
		try {
			$id = $parameters['id'];
			(new Subscription\EditForm(
				$this->subscription($id),
				$this->url,
				$this->csrf,
				$this->backup
			))->submit(function() use($subscription, $id) {
				(new Subscribing\StoredSubscription(
					$id, $this->database
				))->edit(
					new Time\TimeInterval(
						new \DateTimeImmutable(),
						new \DateInterval(
							sprintf('PT%dM', $subscription['interval'])
						)
					)
				);
			});
			$this->flashMessage('Subscription has been edited', 'success');
			$this->redirect('subscriptions');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect($this->url->path());
		}
	}

	private function subscription(int $id): Subscribing\Subscription {
		return new Subscribing\OwnedSubscription(
			new Subscribing\StoredSubscription($id, $this->database),
			$id,
			$this->user,
			$this->database
		);
	}
}