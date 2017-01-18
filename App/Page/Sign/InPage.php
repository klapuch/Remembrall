<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\{
	Access, Output, Form
};
use Remembrall\Control;
use Remembrall\Page;

final class InPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->appendChild($dom->createElement('forms'));
		return new Output\MergedXml(
			$dom,
			new \SimpleXMLElement(
				sprintf(
					'<form name="in">%s</form>',
					(new Control\SignInForm(
						$this->url,
						$this->csrf,
						$this->storage
					))->render()
				)
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
}