<?php
namespace Remembrall\Page;

use GuzzleHttp;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Subscribing
};

final class PartsPage extends BasePage {
	public function createComponentPartForm() {
		$form = new Form();
		$form->addText('url', 'URL');
		$form->addText('expression', 'XPath expression');
		$form->addSubmit('act', 'Act');
		$form->onSuccess[] = function(Form $form, ArrayHash $values) {
			$this->succeededPartForm($form, $values);
		};
		return $form;
	}

	public function succeededPartForm(Form $form, ArrayHash $values) {
		try {

		} catch(Exception\ExistenceException $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		}
	}

	public function renderDefault() {
	}
}
