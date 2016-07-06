<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Remembrall\Component;
use Remembrall\Model\{
	Access, Subscribing
};

final class ReportsPage extends BasePage {
	public function createComponentReports() {
		return new Component\Reports(
			new Subscribing\LoggedReports(
				new Subscribing\OwnedReports(
					$this->subscriber,
					$this->database
				),
				$this->logger
			)
		);
	}
}
