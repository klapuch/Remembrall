<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Klapuch\Application;
use Klapuch\Output;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Remembrall\Response;
use Texy;

final class UnreliablePage extends Layout {
	public function template(array $parameters): Output\Template {
		try {
			return new Application\HtmlTemplate(
				new Response\WebAuthentication(
					new Response\ComposedResponse(
						new Response\CombinedResponse(
							new Response\PlainResponse(
								(new Page(
									new Web\FormattedParts(
										new Web\UnreliableParts(
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
						__DIR__ . '/templates/unreliable.xml',
						__DIR__ . '/../templates/layout.xml'
					),
					$this->user,
					$this->url
				),
				__DIR__ . '/templates/unreliable.xsl'
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						$this->url
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}