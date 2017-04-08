<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Access;
use Klapuch\Output;
use Remembrall\Page;

final class ConfirmPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		try {
			(new Access\ExistingVerificationCode(
				new Access\ThrowawayVerificationCode(
					$parameters['code'],
					$this->database
				),
				$parameters['code'],
				$this->database
			))->use();
			$this->flashMessage('Your code has been confirmed', 'success');
			$user = (new Access\WelcomingEntrance(
				$this->database
			))->enter([$parameters['code']]);
			session_regenerate_id(true);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('subscription');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}