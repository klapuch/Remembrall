<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

/**
 * All pages
 */
final class MySqlPages implements Pages {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function add(Page $page) {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			(?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)',
			$page->url(),
			$page->content()->saveHTML()
		);
	}

	public function iterate(): array {
		return array_reduce(
			$this->database->fetchAll('SELECT url, content FROM pages'),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPage($row['url'], $row['content']);
				return $previous;
			}
		);
	}
}