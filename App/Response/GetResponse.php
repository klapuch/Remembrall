<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

final class GetResponse implements Application\Response {
	public function body(): Output\Format {
		return (new Output\Xml([], 'request'))->with('get', $_GET);
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		];
	}
}