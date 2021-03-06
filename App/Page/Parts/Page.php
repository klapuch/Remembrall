<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Remembrall\Model\Misc;
use Remembrall\Model\Web;

final class Page {
	private const COLUMNS = ['url', 'expression', 'occurrences', 'language'];
	private const DEFAULT_PER_PAGE = 100;
	private const START_PER_PAGE = 10;
	private $parts;

	public function __construct(Web\Parts $parts) {
		$this->parts = $parts;
	}

	public function render(): Output\Format {
		$page = intval($_GET['page'] ?? 1);
		$perPage = intval($_GET['per_page'] ?? self::START_PER_PAGE);
		return new Output\CombinedFormat(
			new Output\ValidXml(
				new Misc\XmlPrintedObjects(
					'parts',
					[
						'part' => iterator_to_array(
							$this->parts->all(
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
				$page,
				$perPage,
				$this->parts->count(),
				self::DEFAULT_PER_PAGE
			))->print(new Output\Xml([], 'pagination'))
		);
	}
}