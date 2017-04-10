<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Form;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class EditPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Subscription\EditForm(
					new Subscribing\OwnedSubscription(
						new Subscribing\StoredSubscription(
							$parameters['id'],
							$this->database
						),
						$parameters['id'],
						$this->user,
						$this->database
					),
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
			(new Form\HarnessedForm(
				new Subscription\EditForm(
					new Subscribing\FakeSubscription(),
					$this->url,
					$this->csrf,
					$this->backup
				),
				$this->backup,
				function() use ($subscription, $id): void {
					(new Subscribing\StoredSubscription(
						$id,
						$this->database
					))->edit(
						new Time\TimeInterval(
							new \DateTimeImmutable(),
							new \DateInterval(
								sprintf('PT%dM', $subscription['interval'])
							)
						)
					);
				}
			))->validate();
			$this->flashMessage('Subscription has been edited', 'success');
			$this->redirect('subscriptions');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect($this->url->path());
		}
	}
}