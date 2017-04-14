<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\UI;

final class FlashResponse implements Application\Response {
	public function body(): Output\Format {
		return new Output\WrappedXml(
			'flashMessages',
			(new UI\FlashMessages(
				new UI\PersistentFlashMessage($_SESSION)
			))->print(new Output\Xml([], 'flashMessage'))
		);
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		];
	}
}