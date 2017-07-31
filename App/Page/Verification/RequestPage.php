<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Remembrall\Form\Verification;
use Remembrall\Page;
use Remembrall\Response;

final class RequestPage extends Page\Layout {
	public function response(array $parameters): Output\Template {
		return new Application\HtmlTemplate(
			new Response\AuthenticatedResponse(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\FormResponse(
							new Verification\RequestForm(
								$this->url,
								$this->csrf,
								new Form\Backup($_SESSION, $_POST)
							)
						),
						new Response\FlashResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/request.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/request.xsl'
		);
	}
}