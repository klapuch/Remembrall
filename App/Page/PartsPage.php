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
			new Access\MySqlSubscriber(
				$this->user->getId(),
				$this->database
			),
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
						new Access\MySqlSubscriber(
							$this->user->getId(),
							$this->database
						),
						new Subscribing\CollectiveParts($this->database)
					),
					new Subscribing\LoggedReports(
						new Subscribing\OwnedReports(
							new Access\MySqlSubscriber(
								$this->user->getId(),
								$this->database
							), $this->database
						),
						$this->logger
					)
				),
				$this->logger
			),
			new Access\MySqlSubscriber($this->user->getId(), $this->database),
			$this->database
		);
	}

	public function renderDefault() {
	}
}
