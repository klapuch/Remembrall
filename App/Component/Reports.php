<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Remembrall\Model\Subscribing;

final class Reports extends SecureControl {
	private $reports;

	public function __construct(Subscribing\Reports $reports) {
		$this->reports = $reports;
		parent::__construct();
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Reports.latte');
		$this->template->reports = $this->reports->iterate();
		$this->template->render();
	}
}
