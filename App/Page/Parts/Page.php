<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page\BasePage;

abstract class Page extends BasePage {
	private const COLUMNS = ['url', 'expression', 'occurrences'];
	private const DEFAULT_PER_PAGE = 100;
	private const START_PER_PAGE = 10;

	abstract protected function parts(): Subscribing\Parts;

	final public function render(array $parameters): Output\Format {
		$page = intval($_GET['page'] ?? 1);
		$perPage = intval($_GET['per_page'] ?? self::START_PER_PAGE);
		$parts = $this->parts();
		return new Output\CombinedFormat(
			new Output\ValidXml(
				new Misc\XmlPrintedObjects(
					'parts',
					[
						'part' => iterator_to_array(
							$parts->iterate(
								new Dataset\CombinedSelection(
									new Dataset\SqlRestSort($_GET['sort'] ?? '', self::COLUMNS),
									new Dataset\SqlPaging($page, $perPage, self::DEFAULT_PER_PAGE)
								)
							)
						),
					]
				),
				__DIR__ . '/templates/constraint.xsd'
			),
			(new UI\AttainablePagination(
				$page, $perPage, $parts->count(), self::DEFAULT_PER_PAGE
			))->print(new Output\Xml([], 'pagination'))
		);
	}
}