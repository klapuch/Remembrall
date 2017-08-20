<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SamplePart implements Sample {
	private const LANGUAGES = ['xpath', 'css'];
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(?, ROW(?, ?), ?, ?)'
		);
		$stmt->execute(
			[
				sprintf(
					'https://www.%s.com',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				sprintf(
					'//%s',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				self::LANGUAGES[array_rand(self::LANGUAGES)],
				bin2hex(random_bytes(10)),
				bin2hex(random_bytes(5)),
			]
		);
	}
}