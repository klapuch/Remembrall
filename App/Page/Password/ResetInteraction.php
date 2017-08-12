<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class ResetInteraction extends Page\Layout {
	public function template(array $credentials): Output\Template {
		try {
			(new Form\HarnessedForm(
				new Password\ResetForm(
					$credentials['reminder'],
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					(new Access\ExpirableRemindedPassword(
						$credentials['reminder'],
						$this->database,
						new Access\RemindedPassword(
							$credentials['reminder'],
							$this->database,
							new Access\UserPassword(
								new Access\ForgetfulUser(
									$credentials['reminder'],
									$this->database
								),
								$this->database,
								new Encryption\AES256CBC(
									$this->configuration['KEYS']['password']
								)
							)
						)
					))->change($credentials['password']);
				}
			))->validate();
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/in')
					),
					['success' => 'Password has been reset'],
					$_SESSION
				)
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