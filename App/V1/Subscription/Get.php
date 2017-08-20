<?php
declare(strict_types = 1);
namespace Remembrall\V1\Subscription;

use Klapuch\Application;
use Klapuch\Output;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class Get extends Page\Api {
	public function template(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\XmlResponse(
					new Response\CachedResponse(
						new Response\ApiAuthentication(
							new Response\PlainResponse(
								new Output\ValidXml(
									(new Subscribing\HarnessedSubscription(
										new Subscribing\OwnedSubscription(
											new Subscribing\StoredSubscription(
												$parameters['id'],
												$this->database
											),
											$parameters['id'],
											$this->user,
											$this->database
										),
										new Misc\ApiErrorCallback(403)
									))->print(new Output\Xml([], 'subscription')),
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