<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Model\Unit;

use Klapuch\Encryption;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AES256CBC extends Tester\TestCase {
	/** @var Encryption\Cipher */
	private $cipher;
	const LENGTH = 160;
	const KEY = '\x1d\x6b\x3e\x91\x66\xdf\xb9\x90\x80\xf5\x03\xac\x6a\x3b\xcd\xae';
	const ENCRYPTED_TEXT = 'bcad70ccfe7af1ec984f8d99ba137db8d22ff724596e95954267edc1284a21127bda038a115052bf78ef15cebce6879b7d3b7ac1b3d90d1055ec36711759dadfd1e098cf944e250380cab2e3d5befa73';
	const DECRYPTED_TEXT = '123456';

	protected function setUp() {
		parent::setUp();
		$this->cipher = new Encryption\AES256CBC(self::KEY);
	}

	public function testCorrectEncryption() {
		Assert::true(
			$this->cipher->decrypt(
				self::DECRYPTED_TEXT,
				self::ENCRYPTED_TEXT
			)
		);
		Assert::false($this->cipher->deprecated(self::ENCRYPTED_TEXT));
	}

	/**
	 * @dataProvider plainTexts
	 */
	public function testValidFormats($password) {
		Assert::same(
			self::LENGTH,
			mb_strlen($this->cipher->encrypt($password), 'UTF-8')
		);
	}

	/**
	 * @dataProvider plainTexts
	 */
	public function testCorrectEncryptions($password) {
		Assert::true(
			$this->cipher->decrypt(
				$password,
				$this->cipher->encrypt($password)
			)
		);
	}

	public function testHashWithLowCost() {
		Assert::true(
			$this->cipher->deprecated(
				'8dd393444045baf2fc34588d4b0728f579937f6219a9270f7abab27d21a527dbe672837193ea40c7bd263657a519f1d0a0fad844edc3de041c9aa78c8653e58b658ac68d79a2cb3613680bfa3fdb6f1c'
			)
		);
	}

	protected function plainTexts() {
		return [
			[''],
			['0'],
			['foo'],
			[hash('SHA512', time())],
		];
	}
}

(new AES256CBC())->run();
