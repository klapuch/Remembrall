<?php
declare(strict_types = 1);
namespace Remembrall\Page\Invitation;

use Klapuch\Application;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class AcceptPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		try {
			(new Subscribing\UnusedInvitation(
				new Subscribing\ParticipantInvitation(
					$parameters['code'],
					$this->database
				),
				$parameters['code'],
				$this->database
			))->accept();
			$this->flashMessage('Invitation has been accepted', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'sign/in')
			);
		}
	}
}