<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

final class PermissionResponse implements Application\Response {
	private const PERMISSION = __DIR__ . '/../Configuration/permission.xml';

	public function body(): Output\Format {
		return new Output\RemoteXml(self::PERMISSION);
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		];
	}
}