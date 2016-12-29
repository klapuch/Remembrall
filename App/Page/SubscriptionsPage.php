<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Output;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;

final class SubscriptionsPage extends BasePage {
	public function renderDefault(): \SimpleXMLElement {
		return new \SimpleXMLElement(
			(string)new Output\WrappedXml(
				'subscriptions',
				...(new Subscribing\OwnedSubscriptions(
					$this->subscriber,
					$this->database
				))->print(new Output\Xml([], 'subscription'))
			)
		);
	}

	public function actionDelete(array $parameters): void {
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