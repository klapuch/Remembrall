<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Output;
use Klapuch\Dataset;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Misc;
use Remembrall\Page;
use Texy;

final class PopularPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				['part' => array_map(
					function(Subscribing\Part $origin): Subscribing\Part {
						return new Subscribing\FormattedPart(
							$origin, new Texy\Texy()
						);
					},
					iterator_to_array(
						(new Subscribing\PopularParts(
							new Subscribing\CollectiveParts($this->database),
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