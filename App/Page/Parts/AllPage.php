<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\{
	Output, Dataset
};
use Remembrall\Model\{
	Subscribing, Misc
};
use Remembrall\Page;

final class AllPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				['part' =>
					iterator_to_array(
						(new Subscribing\CollectiveParts(
							$this->database
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