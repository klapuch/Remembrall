<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Remembrall\Form\Subscription;
use Remembrall\Page;
use Remembrall\Response;

final class DefaultPage extends Page\Layout {
	public function response(array $parameters): Output\Template {
		return new Application\HtmlTemplate(
			new Response\AuthenticatedResponse(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\FormResponse(
							new Subscription\PreviewForm(
								$this->url,
								$this->csrf,
								new Form\Backup($_SESSION, $_POST)
							)
						),
						new Response\FlashResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/default.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/default.xsl'
		);
	}
}