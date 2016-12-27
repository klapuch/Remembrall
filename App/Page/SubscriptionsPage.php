<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Output;
use Mockery\CountValidator\Exception;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;

final class SubscriptionsPage extends BasePage {
	public function renderDefault() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Subscriptions/default.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Subscriptions/default.xsl',
			new Output\MergedXml(
				$xml,
				new \SimpleXMLElement(
					(string)new Output\WrappedXml(
						'subscriptions',
						...(new Subscribing\OwnedSubscriptions(
							$this->subscriber,
							$this->database
						))->print(new Output\Xml([], 'subscription'))
					)
				),
				...$this->layout()
			)
		))->render();
	}

	public function renderDelete(array $parameters) {
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