<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Encryption;
use Klapuch\Output;
use Remembrall\Form;
use Remembrall\Form\Password;
use Remembrall\Page;

final class ResetPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Password\ResetForm(
					$parameters['reminder'],
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitReset(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Password\ResetForm(
					$credentials['reminder'],
					$this->url,
					$this->csrf,
					$this->backup
				),
				$this->backup,
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