<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Nette\Security;
use Remembrall\Model\{
	Access, Subscribing
};

final class Parts extends SecureControl {
	private $parts;

	public function __construct(Subscribing\Parts $parts) {
		$this->parts = $parts;
		parent::__construct();
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Parts.latte');
		$this->template->parts = $this->parts->iterate();
		$this->template->render();
	}

	public function handleRemove(string $url, string $expression) {
		$this->parts->remove(
			new Subscribing\FakePart(
				'',
				new Subscribing\FakePage($url),
				false,
				new Subscribing\FakeExpression($expression)
			)
		);
		if(!$this->presenter->isAjax()) {
			$this->presenter->flashMessage('Part has been deleted', 'success');
			$this->presenter->redirect('this');
		}
		$this->redrawControl();
	}
}
