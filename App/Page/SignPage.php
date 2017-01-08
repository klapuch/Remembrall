<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Access, Output
};
use Remembrall\Control;

final class SignPage extends BasePage {
	public function renderIn(): \SimpleXMLElement {
		return new \SimpleXMLElement(
			sprintf(
				'<forms><form name="in">%s</form></forms>',
				(new Control\SignInForm(
					$this->url,
					$this->csrf,
					$this->storage
				))->render()
			)
		);
	}

	public function submitIn(array $credentials): void {
		try {
			(new Control\SignInForm(
				$this->url,
				$this->csrf,
				$this->storage
			))->validate();
			$user = (new Access\SecureEntrance(
				$this->database,
				$this->cipher
			))->enter([$credentials['email'], $credentials['password']]);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('subscriptions');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}

	public function actionOut(): void {
		try {
			if(!isset($_SESSION['id']))
				throw new \Exception('You are not logged in');
			unset($_SESSION['id']);
			$this->flashMessage('You have been logged out', 'success');
			$this->redirect('sign/in');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}