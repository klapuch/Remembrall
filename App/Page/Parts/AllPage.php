<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Texy;

final class AllPage extends Page\BasePage {
	private const MAX_PER_PAGE = 100;

	public function render(array $parameters): Output\Format {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$parts = new Subscribing\FormattedParts(
			new Subscribing\CollectiveParts($this->database),
			new Texy\Texy()
		);
		return new Output\CombinedFormat(
			new Output\ValidXml(
				new Misc\XmlPrintedObjects(
					'parts',
					[
						'part' => iterator_to_array(
							$parts->iterate(
								new Dataset\CombinedSelection(
									new Dataset\SqlRestSort($_GET['sort'] ?? ''),
									new Dataset\SqlPaging($page, $perPage, self::MAX_PER_PAGE)
								)
							)
						),
					]
				),
				__DIR__ . '/templates/constraint.xsd'
			),
			(new UI\AttainablePagination(
				$page, $perPage, $parts->count()
			))->print(new Output\Xml([], 'pagination'))
		);
	}
}