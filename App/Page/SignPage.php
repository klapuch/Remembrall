<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Access, Form, Output
};

final class SignPage extends BasePage {
	private const COLUMNS = 5;

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
						(new Form\RawForm(['method' => 'POST', 'action' => 'in', 'role' => 'form', 'class' => 'form-horizontal'],
							new Form\CsrfInput($_SESSION, $_POST),
							new Form\BootstrapInput(
								new Form\BoundControl(
									new Form\SafeInput([
										'type' => 'email',
										'name' => 'email',
										'class' => 'form-control',
										'required' => 'required',
									], $this->backup),
									new Form\LinkedLabel('Email', 'email')
								),
								self::COLUMNS
							),
							new Form\BootstrapInput(
								new Form\BoundControl(
									new Form\SafeInput([
										'type' => 'password',
										'name' => 'password',
										'class' => 'form-control',
										'required' => 'required',
									], $this->backup),
									new Form\LinkedLabel('Password', 'password')
								),
								self::COLUMNS
							),
							new Form\BootstrapInput(
								new Form\SafeInput([
									'type' => 'submit',
									'name' => 'act',
									'class' => 'form-control',
									'value' => 'Login',
								], $this->backup),
								self::COLUMNS
							)
						))->render()
					)
				),
				...$this->layout()
			)
		))->render();
	}

	public function actionIn(array $credentials) {
		try {
			$this->protect();
			$user = (new Access\SecureEntrance(
				$this->database,
				$this->cipher
			))->enter([$credentials['email'], $credentials['password']]);
			$_SESSION['id'] = $user->id();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('parts');
		} catch(\Exception $ex) {
			$this->backup['email'] = $credentials['email'];
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}

	public function renderOut() {
		try {
			if(!isset($_SESSION['id'])) {
				throw new \Exception('You are not logged in');
			}
			unset($_SESSION['id']);
			$this->flashMessage('You have been logged out', 'success');
			$this->redirect('sign/in');
		} catch(\Exception $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}
