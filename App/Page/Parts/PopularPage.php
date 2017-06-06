<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Klapuch\Application;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Remembrall\Response;
use Texy;

final class PopularPage extends Layout {
	public function response(array $parameters): Application\Response {
		try {
			return new Response\AuthenticatedResponse(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\PlainResponse(
							(new Page(
								new Web\FormattedParts(
									new Web\PopularParts(
										new Web\CollectiveParts($this->database),
										$this->database
									),
									new Texy\Texy(),
									new Dindent\Indenter()
								)
							))->render()
						),
						new Response\FlashResponse(),
						new Response\GetResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/popular.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					$this->url
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}