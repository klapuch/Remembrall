<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Output;
use Remembrall\Control;
use Remembrall\Page;

final class InPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms><form name="in">%s</form></forms>',
				(new Control\SignInForm(
					$this->url,
					$this->csrf,
					$this->storage
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
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
			session_regenerate_id(true);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('subscriptions');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}