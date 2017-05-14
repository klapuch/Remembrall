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
			(new Access\SessionEntrance(
				new Access\FakeEntrance(new Access\Guest()),
				$_SESSION,
				new class implements Internal\Extension {
					function improve(): void {
					}
				}
			))->exit();
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'sign/in')
				),
				['success' => 'You have been logged out'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'sign/in')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}