<?php
declare(strict_types = 1);
namespace Remembrall\Model\Security;

final class FakeCipher implements Cipher {
	private $decrypt;
	private $deprecated;
	private $encrypt;

	public function __construct(bool $decrypt = true, bool $deprecated = false, string $encrypt = 'secret') {
		$this->decrypt = $decrypt;
		$this->deprecated = $deprecated;
		$this->encrypt = $encrypt;
	}

	public function encrypt(string $plain): string {
		return $this->encrypt;
	}

	public function decrypt(string $plain, string $hash): bool {
		return $this->decrypt;
	}

	public function deprecated(string $hash): bool {
		return $this->deprecated;
	}
}