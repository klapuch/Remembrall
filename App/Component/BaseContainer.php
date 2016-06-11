<?php
namespace Remembrall\Component;

use Nette;
use Nette\Application\UI;
use Nette\Forms;

abstract class BaseContainer extends Forms\Container {
	public function __construct() {
		parent::__construct();
		$this->monitor('Nette\Forms\Form');
	}

	protected function attached($obj) {
		parent::attached($obj);
		if($obj instanceof Forms\Form) {
			$this->currentGroup = $this->form->currentGroup;
			$this->configure();
		}
	}

	abstract protected function configure();
}