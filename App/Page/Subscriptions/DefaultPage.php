<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Output;
use Klapuch\Dataset;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Misc;
use Remembrall\Page;
use Texy;

final class DefaultPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'subscriptions',
				['subscription' => array_map(
					function(Subscribing\Subscription $origin): Subscribing\Subscription {
						return new Subscribing\FormattedSubscription(
							$origin, new Texy\Texy()
						);
					},
					iterator_to_array(
						(new Subscribing\OwnedSubscriptions(
							$this->user,
							$this->database
						))->iterate(
							new Dataset\CombinedSelection(
								new Dataset\SqlRestSort($_GET['sort'] ?? '')
							)
						)
					)
				),
			]
		),
		__DIR__ . '/templates/constraint.xsd'
	);
	}
}