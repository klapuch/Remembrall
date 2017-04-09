<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DeletePage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		$this->redirect('error');
	}

	public function submitDelete(array $subscription, array $parameters): void {
		try {
			$this->protect();
			(new Subscribing\OwnedSubscription(
				new Subscribing\StoredSubscription(
					(int) $subscription['id'],
					$this->database
				),
				(int) $subscription['id'],
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