<?php
declare(strict_types = 1);
namespace Remembrall\Page\Error;

use Klapuch\Output;
use Remembrall\Page;

final class DefaultPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		http_response_code(500);
		return new Output\FakeFormat();
	}
}