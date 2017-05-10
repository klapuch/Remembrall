<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class RedirectResponse implements Application\Response {
	private $origin;
	private $uri;

	public function __construct(Application\Response $origin, Uri\Uri $uri) {
		$this->origin = $origin;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		return [
				'Location' => sprintf('%s/%s', $this->uri->reference(), $this->uri->path()),
			] + $this->origin->headers();
	}
}