<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

final class WebPages implements Pages {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function add(string $url, Page $page): Page {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			(?, ?) ON DUPLICATE KEY UPDATE
			content = VALUES(content)',
			$url,
			$page->content()->saveHTML()
		);
		return $page;
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll('SELECT url, content FROM pages'),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPage($row['content'], $row['url']);
				return $previous;
			}
		);
	}
}