<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Remembrall\Form\Sign;
use Remembrall\Page;
use Remembrall\Response;

final class InPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		return new Application\HtmlTemplate(
			new Response\WebAuthentication(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\FormResponse(
							new Sign\InForm(
								$this->url,
								$this->csrf,
								new Form\Backup($_SESSION, $_POST)
							)
						),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user),
						new Response\FlashResponse()
					),
					__DIR__ . '/templates/in.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/in.xsl'
		);
	}
}