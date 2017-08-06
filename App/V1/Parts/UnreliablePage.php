<?php
declare(strict_types = 1);
namespace Remembrall\V1\Parts;

use Klapuch\Application;
use Klapuch\Output;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Remembrall\Response;

final class UnreliablePage extends Layout {
	public function response(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\XmlResponse(
					new Response\CachedResponse(
						new Response\AuthenticatedResponse(
							new Response\PlainResponse(
								(new Page(
									new Web\UnreliableParts(
										new Web\CollectiveParts($this->database),
										$this->database
									)
								))->render()
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