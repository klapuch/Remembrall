<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DefaultPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Output\WrappedXml(
				'subscriptions',
				...array_map(
					function(Subscribing\Subscription $part): Output\Format {
						return $part->print(new Output\Xml([], 'subscription'));
					},
					iterator_to_array(
						new Subscribing\OwnedSubscriptions(
							$this->user,
							$this->database
						)
					)
				)
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}