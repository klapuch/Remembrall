<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Uri;
use Remembrall\Page;
use Remembrall\Response;

final class OutPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		try {
			if (!isset($_SESSION['id']))
				throw new \Exception('You are not logged in');
			(new Access\SessionEntrance(
				new Access\FakeEntrance(new Access\Guest()),
				$_SESSION,
				new class implements Internal\Extension {
					function improve(): void {
					}
				}
			))->exit();
			$this->flashMessage('You have been logged out', 'success');
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