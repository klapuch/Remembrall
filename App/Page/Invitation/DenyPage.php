<?php
declare(strict_types = 1);
namespace Remembrall\Page\Invitation;

use Klapuch\Application;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DenyPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		try {
			(new Subscribing\UnusedInvitation(
				new Subscribing\ParticipantInvitation(
					$parameters['code'],
					$this->database
				),
				$parameters['code'],
				$this->database
			))->deny();
			$this->flashMessage('Invitation has been denied', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->redirect('sign/in');
		}
	}
}