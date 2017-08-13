<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Klapuch\Access;

final class TestUsers implements Access\Users {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function register(string $email = '', string $password = '', string $role = ''): Access\User {
		$stmt = $this->database->prepare(
			"INSERT INTO users (id, email, password, role) VALUES
			(1, ?, 'dc98d5af8f15840afcab387d5923f330df4a7bc76625e024fec2cb1f626543dccf352999ffd4e3c15047bee301104d06651ccaaee60ed3b98723b1e04cbaa429e00f088976bd9b5a94d5863f1d124ee8', 'member')"
		);
		$stmt->execute([$email ?: 'klapuchdominik@gmail.com']);
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used, used_at) VALUES
			(1, 'c7fb39e3b3e0d9efa6fce134b703fcea5c4c4196cef0dcaccf:3b59944087428cd5b95be4f18dcf06b8815b9fa6', TRUE, NOW());"
		);
		return new Access\ConstantUser('1', []);
	}
}