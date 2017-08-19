<?php
declare(strict_types = 1);
namespace Remembrall\V1\Subscriptions;

use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class Get extends Page\Api {
	private const FIELDS = ['last_update', 'interval', 'expression', 'url', 'language'];

	public function template(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\XmlResponse(
					new Response\CachedResponse(
						new Response\ApiAuthentication(
							new Response\PlainResponse(
								new Output\ValidXml(
									new Misc\XmlPrintedObjects(
										'subscriptions',
										[
											'subscription' => iterator_to_array(
												(new Subscribing\OwnedSubscriptions(
													$this->user,
													$this->database
												))->all(
													new Dataset\CombinedSelection(
														new Dataset\SqlRestSort(
															$_GET['sort'] ?? '',
															self::FIELDS
														)
													)
												)
											),
										]
									),
									__DIR__ . '/schema/constraint.xsd'
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
}