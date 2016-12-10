<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Access, Output
};
use Remembrall\Control;

final class SignPage extends BasePage {
	public function renderIn() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Sign/in.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Sign/in.xsl',
			new Output\MergedXml(
				$xml,
				new \SimpleXMLElement(
					sprintf(
						'<forms><in>%s</in></forms>',
						(new Control\SignInForm($this->csrf, $this->storage))->render()
					)
				),
				...$this->layout()
			)
		))->render();
	}

	public function actionIn(array $credentials) {
		try {
			(new Control\SignInForm($this->csrf, $this->storage))->validate();
			$user = (new Access\SecureEntrance(
				$this->database,
				$this->cipher
			))->enter([$credentials['email'], $credentials['password']]);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('parts');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}

	public function renderOut() {
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