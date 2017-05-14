<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\UI;

final class InformativeResponse implements Application\Response {
	private $origin;
	private $message;
	private $sessions;

	public function __construct(
		Application\Response $origin,
		array $message,
		array &$sessions
	) {
		$this->origin = new CachedResponse($origin);
		$this->message = $message;
		$this->sessions = &$sessions;
	}

	public function body(): Output\Format {
		 return $this->origin->body();
	}

	public function headers(): array {
		$headers = $this->origin->headers();
		(new UI\PersistentFlashMessage(
			$this->sessions
		))->flash(current($this->message), key($this->message));
		return $headers;
	}
}