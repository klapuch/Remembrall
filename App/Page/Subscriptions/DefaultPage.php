<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Klapuch\Dataset;
use Klapuch\Output;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Texy;

final class DefaultPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'subscriptions',
				[
					'subscription' => iterator_to_array(
						(new Subscribing\FormattedSubscriptions(
							new Subscribing\OwnedSubscriptions(
								$this->user,
								$this->database
							),
							new Texy\Texy()
						))->iterate(
							new Dataset\CombinedSelection(
								new Dataset\SqlRestSort($_GET['sort'] ?? '')
							)
						)
					),
				]
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}