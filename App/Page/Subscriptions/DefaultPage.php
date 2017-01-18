<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Output;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DefaultPage extends Page\BasePage {
	public function render(): \SimpleXMLElement {
		return new \SimpleXMLElement(
			(new Output\ValidXml(
				new Output\WrappedXml(
					'subscriptions',
					...(new Subscribing\OwnedSubscriptions(
						$this->subscriber,
						$this->database
					))->print(new Output\Xml([], 'subscription'))
				),
				__DIR__ . '/templates/constraint.xsd'
			))->serialization()
		);
	}
}