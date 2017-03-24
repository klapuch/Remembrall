<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DeletePage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		try {
			$this->protect();
			(new Subscribing\OwnedSubscription(
				new Subscribing\StoredSubscription(
					$parameters['id'],
					$this->database
				),
				$parameters['id'],
				$this->user,
				$this->database
			))->cancel();
			$this->flashMessage('Subscription has been deleted', 'success');
			$this->redirect('subscriptions');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('subscriptions');
		}
	}
}