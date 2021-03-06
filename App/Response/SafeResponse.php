<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class SafeResponse implements Application\Response {
	private $origin;
	private $fallback;
	private $sessions;

	public function __construct(
		Application\Response $origin,
		Uri\Uri $fallback,
		array &$sessions
	) {
		$this->origin = new CachedResponse($origin);
		$this->fallback = $fallback;
		$this->sessions = &$sessions;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		try {
			$this->body();
		} catch (\Throwable $ex) {
			return (new InformativeResponse(
				new RedirectResponse(
					$this->origin,
					$this->fallback
				),
				['danger' => $ex->getMessage()],
				$this->sessions
			))->headers();
		}
		return $this->origin->headers();
	}
}