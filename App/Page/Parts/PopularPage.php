<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Output;
use Remembrall\Model\{
	Subscribing, Misc
};
use Remembrall\Page;

final class PopularPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				['part' => 
					iterator_to_array(
						new Subscribing\PopularParts(
							new Subscribing\CollectiveParts($this->database),
							$this->database
						)
					),
				]
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}