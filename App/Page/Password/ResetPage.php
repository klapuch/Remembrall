<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class ResetPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		try {
			(new Access\ValidReminderRule(
				$this->database
			))->apply($parameters['reminder']);
			return new Response\AuthenticatedResponse(
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
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'password/remind')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}

	public function submitReset(array $credentials): Application\Response {
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
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'sign/in')
				),
				['success' => 'Password has been reset'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			var_dump($ex->getMessage());
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'password/remind')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}