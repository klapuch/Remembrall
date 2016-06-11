<?php
namespace Remembrall\Component;

use Nette\Application\UI;

final class BaseForm extends UI\Form {
	public function __construct() {
		parent::__construct();
		$this->addProtection();
	}

	public function fireEvents() {
		$this->onError[] = function() {
			$this->presenter->flashMessage(current($this->errors), 'danger');
		};
		parent::fireEvents();
	}
}