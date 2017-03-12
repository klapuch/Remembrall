<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Output;
use Remembrall\Page;

final class OutPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		try {
			if (!isset($_SESSION['id']))
				throw new \Exception('You are not logged in');
			unset($_SESSION['id']);
			$this->flashMessage('You have been logged out', 'success');
			$this->redirect('sign/in');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}