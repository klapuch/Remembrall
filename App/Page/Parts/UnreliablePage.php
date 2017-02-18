<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Dataset;
use Klapuch\Output;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Texy;

final class UnreliablePage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				[
					'part' => iterator_to_array(
						(new Subscribing\FormattedParts(
							new Subscribing\UnreliableParts(
								new Subscribing\CollectiveParts($this->database),
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