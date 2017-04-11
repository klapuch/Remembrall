<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Output;
use Remembrall\Page;

final class OutPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		try {
			if (!isset($_SESSION['id']))
				throw new \Exception('You are not logged in');
			(new Access\SessionEntrance(
				new Access\FakeEntrance(new Access\Guest()),
				$_SESSION
			))->exit();
			$this->flashMessage('You have been logged out', 'success');
			$this->redirect('sign/in');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}