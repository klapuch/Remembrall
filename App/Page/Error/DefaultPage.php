<?php
declare(strict_types = 1);
namespace Remembrall\Page\Error;

use Klapuch\Application;
use Remembrall\Page;
use Remembrall\Response;

final class DefaultPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		http_response_code(500);
		return new Response\ComposedResponse(
			new Response\GetResponse(),
			__DIR__ . '/templates/default.xml',
			__DIR__ . '/../templates/layout.xml'
		);
	}
}