<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class RemindPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		return new Application\HtmlTemplate(
			new Response\WebAuthentication(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\FormResponse(
							new Password\RemindForm(
								$this->url,
								$this->csrf,
								new Form\Backup($_SESSION, $_POST)
							)
						),
						new Response\FlashResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/remind.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/remind.xsl'
		);
	}
}