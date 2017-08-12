<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Authorization;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;

final class WebAuthentication implements Application\Response {
	private const PERMISSION = __DIR__ . '/../Configuration/Permissions/web.xml';
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
		return $this->origin->body();
	}

	public function headers(): array {
		$role = new Authorization\HttpRole(
			new Authorization\RolePermissions(
				$this->user->properties()['role'] ?? 'guest',
				new Authorization\XmlPermissions(self::PERMISSION)
			)
		);
		if (!$role->allowed($this->uri->path())) {
			http_response_code(403);
			(new UI\PersistentFlashMessage(
				$_SESSION
			))->flash('You are not allowed to see the page.', 'danger');
			return [
				'Location' => sprintf(
					'%s/%s',
					$this->uri->reference(),
					$this->uri->path() === 'sign/in' ? '' : 'sign/in'
				),
			] + $this->origin->headers();
		}
		return $this->origin->headers();
	}
}