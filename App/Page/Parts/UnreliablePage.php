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

final class UnreliablePage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				['part' => 
					iterator_to_array(
						(new Subscribing\UnreliableParts(
							new Subscribing\CollectiveParts($this->database),
							$this->database
						))->iterate(new Dataset\FakeSelection(''))
					),
				]
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}