<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form\Backup;
use Remembrall\Form;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class ResetPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Password\ResetForm(
							$parameters['reminder'],
							$this->url,
							$this->csrf,
							new Backup($_SESSION, $_POST)
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
	}

	public function submitReset(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Password\ResetForm(
					$credentials['reminder'],
					$this->url,
					$this->csrf,
					new Backup($_SESSION, $_POST)
				),
				new Backup($_SESSION, $_POST),
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
			$this->flashMessage('Password has been reset', 'success');
			$this->redirect('sign/in');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('password/remind');
		}
	}
}