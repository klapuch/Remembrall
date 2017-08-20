<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Klapuch\Access;

final class ApiTestUsers implements Access\Users {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function register(string $email = '', string $password = '', string $role = ''): Access\User {
		$user = (new TestUsers($this->database))->register($email, $password, $role);
		session_set_save_handler(
			new class($user) implements \SessionHandlerInterface {
				private $user;

				public function __construct(Access\User $user) {
					$this->user = $user;
				}

				public function close(): bool {
					return true;
				}

				public function destroy($id): bool {
					return true;
				}

				public function gc($maxLifeTime): bool {
					return true;
				}

				public function open($path, $name): bool {
					return true;
				}

				public function read($id): string {
					return sprintf(
						'id|s:%d:"%s";',
						strlen($this->user->id()),
						$this->user->id()
					);
				}

				public function write($id, $data): bool {
					return true;
				}
			},
			true
		);
		$_SESSION['id'] = $user->id();
		$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer 0c3da2dd2900adb00f8f231e4484c1b5';
		return $user;
	}
}