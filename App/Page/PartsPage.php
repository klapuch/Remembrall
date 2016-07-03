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
			$this->user->getIdentity(),
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
				new Subscribing\OwnedParts(
					$this->database,
					new Access\MySqlSubscriber(
						$this->user->getId(),
						$this->database
					),
					new Subscribing\CollectiveParts($this->database)
				),
				$this->logger
			),
			$this->user->getIdentity()
		);
	}

	public function renderDefault() {
	}
}
