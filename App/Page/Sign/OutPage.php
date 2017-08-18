<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Page;
use Remembrall\Response;

final class OutPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		try {
			(new Access\SessionEntrance(
				new Access\FakeEntrance(new Access\Guest()),
				$_SESSION,
				new class implements Internal\Extension {
					function improve(): void {
					}
				}
			))->exit();
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/in')
					),
					['success' => 'You have been logged out'],
					$_SESSION
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/in')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}