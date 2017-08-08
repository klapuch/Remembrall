<?php
declare(strict_types = 1);
namespace Remembrall\V1\Parts;

use Klapuch\Application;
use Klapuch\Output;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Remembrall\Response;
use Klapuch\Dataset;
use Remembrall\Model\Misc;

final class Page extends Layout {
	private const COLUMNS = ['url', 'expression', 'occurrences', 'language'];
	private const DEFAULT_PER_PAGE = 100;
	private const START_PER_PAGE = 10;

	public function response(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\XmlResponse(
					new Response\CachedResponse(
						new Response\AuthenticatedResponse(
							new Response\PlainResponse(
								$this->format(
									new Web\SuitedParts(
										$_GET['type'] ?? '',
										$this->database
									)
								)
							),
							$this->user,
							$this->url
						)
					)
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\XmlError($ex));
		}
	}

	private function format(Web\Parts $parts): Output\Format {
		$page = intval($_GET['page'] ?? 1);
		$perPage = intval($_GET['per_page'] ?? self::START_PER_PAGE);
		return new Output\ValidXml(
			new Misc\XmlPrintedObjects(
				'parts',
				[
					'part' => iterator_to_array(
						$parts->all(
							new Dataset\CombinedSelection(
								new Dataset\SqlRestSort($_GET['sort'] ?? '', self::COLUMNS),
								new Dataset\SqlPaging($page, $perPage, self::DEFAULT_PER_PAGE)
							)
						)
					),
				]
			),
			__DIR__ . '/templates/constraint.xsd'
		);
	}
}