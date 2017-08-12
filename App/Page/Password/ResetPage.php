<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class ResetPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		try {
			(new Access\ValidReminderRule(
				$this->database
			))->apply($parameters['reminder']);
			return new Application\HtmlTemplate(
				new Response\WebAuthentication(
					new Response\ComposedResponse(
						new Response\CombinedResponse(
							new Response\FormResponse(
								new Password\ResetForm(
									$parameters['reminder'],
									$this->url,
									$this->csrf,
									new Form\Backup($_SESSION, $_POST)
								)
							),
							new Response\FlashResponse(),
							new Response\PermissionResponse(),
							new Response\IdentifiedResponse($this->user)
						),
						__DIR__ . '/templates/reset.xml',
						__DIR__ . '/../templates/layout.xml'
					),
					$this->user,
					$this->url
				),
				__DIR__ . '/templates/reset.xsl'
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'password/remind')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}