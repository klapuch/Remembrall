<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SamplePart implements Sample {
	private const LANGUAGES = ['xpath', 'css'];
	private $database;
	private $part;

	public function __construct(\PDO $database, array $part = []) {
		$this->database = $database;
		$this->part = $part;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(?, ROW(?, ?), ?, ?)'
		);
		$stmt->execute(
			[
				$this->part['page_url'] ?? sprintf(
					'https://www.%s.com',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				$this->part['expression']['value'] ?? sprintf(
					'//%s',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				$this->part['expression']['language'] ?? self::LANGUAGES[array_rand(self::LANGUAGES)],
				$this->part['content'] ?? bin2hex(random_bytes(10)),
				$this->part['snapshot'] ?? bin2hex(random_bytes(5)),
			]
		);
	}
}