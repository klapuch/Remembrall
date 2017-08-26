<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SamplePage implements Sample {
	private const DOMAINS = ['sk', 'cz', 'com'],
		SCHEMAS = ['https://', 'http://', ''];
	private $database;
	private $page;

	public function __construct(\PDO $database, array $page = []) {
		$this->database = $database;
		$this->page = $page;
	}

	public function try(): void {
		$stmt = $this->database->prepare('INSERT INTO pages (url, content) VALUES (?, ?)');
		$stmt->execute(
			[
				$this->page['url'] ??
				sprintf(
					'%swww.%s.%s',
					self::SCHEMAS[array_rand(self::SCHEMAS)],
					substr(
						uniqid('', true),
						-mt_rand(1, 10)
					),
					self::DOMAINS[array_rand(self::DOMAINS)]
				),
				$this->page['content'] ?? bin2hex(random_bytes(10)),
			]
		);
	}
}