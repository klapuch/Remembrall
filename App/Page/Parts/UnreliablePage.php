<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Klapuch\Application;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Remembrall\Response;
use Texy;

final class UnreliablePage extends Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
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
					new Response\GetResponse(),
					new Response\PermissionResponse(),
					new Response\IdentifiedResponse($this->user)
				),
				__DIR__ . '/templates/unreliable.xml',
				__DIR__ . '/../templates/layout.xml'
			),
			$this->user,
			$this->url
		);
	}
}