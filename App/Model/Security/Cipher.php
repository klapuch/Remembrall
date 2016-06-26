<?php
declare(strict_types = 1);
namespace Remembrall\Model\Security;

interface Cipher {
	/**
	 * Encrypt the given plain text
	 * @param string $plain
	 * @return string
	 */
	public function encrypt(string $plain): string;

	/**
	 * Checks whether the plain text is the same as the hash
	 * @param string $plain
	 * @param string $hash
	 * @return bool
	 */
	public function decrypt(string $plain, string $hash): bool;

	/**
	 * Is the given hash too old for the given cipher and needs to be changed?
	 * @param string $hash
	 * @return bool
	 */
	public function deprecated(string $hash): bool;
}