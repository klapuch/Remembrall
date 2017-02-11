<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Output;
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
						new Subscribing\CollectiveParts($this->database)
					),
				]
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}