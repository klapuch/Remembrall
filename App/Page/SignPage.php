<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Output, Access
};

final class SignPage extends BasePage {
	public function renderIn() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Sign/in.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Sign/in.xsl',
			new Output\MergedXml($xml, ...$this->layout())
		))->render();
	}

	public function actionIn(array $credentials) {
		try {
			$user = (new Access\SecureEntrance(
				$this->database,
				$this->cipher
			))->enter([$credentials['email'], $credentials['password']]);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('parts');
		} catch(\Exception $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}