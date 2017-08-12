<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Authorization;
use Klapuch\Output;
use Klapuch\Uri;

final class ApiAuthentication implements Application\Response {
	private const PERMISSION = __DIR__ . '/../Configuration/Permissions/api.xml';
	private $origin;
	private $user;
	private $uri;

	public function __construct(
		Application\Response $origin,
		Access\User $user,
		Uri\Uri $uri
	) {
		$this->origin = $origin;
		$this->user = $user;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		if ($this->allowed($this->user, $this->uri))
			return $this->origin->body();
		$dom = new \DOMDocument('1.0', 'utf-8');
		$message = $dom->createElement('message');
		$text = $dom->createAttribute('text');
		$text->value = 'You are not allowed to see the response.';
		$message->appendChild($text);
		$dom->appendChild($message);
		return new Output\DomFormat($dom, 'xml');
	}

	public function headers(): array {
		if (!$this->allowed($this->user, $this->uri))
			http_response_code(403);
		return $this->origin->headers();
	}

	/**
	 * Does the user have access to the URI?
	 * @param \Klapuch\Access\User $user
	 * @param \Klapuch\Uri\Uri $uri
	 * @return bool
	 */
	private function allowed(Access\User $user, Uri\Uri $uri): bool {
		return (new Authorization\HttpRole(
			new Authorization\RolePermissions(
				$user->properties()['role'] ?? 'guest',
				new Authorization\XmlPermissions(self::PERMISSION)
			)
		))->allowed($uri->path());
	}
}