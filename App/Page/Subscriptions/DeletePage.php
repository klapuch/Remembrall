<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Output;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DeletePage extends Page\BasePage {
	public function render(array $parameters): \SimpleXMLElement {
		try {
			$this->protect();
			['id' => $id] = $parameters;
			(new Subscribing\OwnedSubscription(
				new Subscribing\StoredSubscription((int)$id, $this->database),
				(int)$id,
				$this->subscriber,
				$this->database
			))->cancel();
			$this->flashMessage('Subscription has been deleted', 'success');
			$this->redirect('subscriptions');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('subscriptions');
		}
	}
}