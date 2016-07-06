<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Remembrall\Component;
use Remembrall\Model\{
	Access, Subscribing
};

final class PartsPage extends BasePage {
	public function createComponentPartForm() {
		$form = new Component\PartForm(
			$this->subscriber,
			$this->database,
			$this->logger
		);
		$form->onSuccess[] = function() {
			$this->flashMessage(
				'Part has been successfully subscribed',
				'success'
			);
			$this->redirect('this');
		};
		return $form;
	}

	public function createComponentParts() {
		return new Component\Parts(
			new Subscribing\LoggedParts(
				new Subscribing\ReportedParts(
					new Subscribing\OwnedParts(
						$this->database,
						$this->subscriber,
						new Subscribing\CollectiveParts($this->database)
					),
					new Subscribing\LoggedReports(
						new Subscribing\OwnedReports(
							$this->subscriber, $this->database
						),
						$this->logger
					)
				),
				$this->logger
			),
			$this->subscriber,
			$this->database
		);
	}

	public function renderDefault() {
	}
}
