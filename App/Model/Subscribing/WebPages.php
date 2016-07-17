<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Storage;

final class WebPages implements Pages {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function add(Page $page): Page {
		(new Storage\Transaction($this->database))->start(
			function() use ($page) {
				$this->database->query(
					'INSERT INTO pages (url, content) VALUES
					(?, ?) ON DUPLICATE KEY UPDATE
					content = VALUES(content)',
					$page->url(),
					$page->content()->saveHTML()
				);
				$this->database->query(
					'INSERT INTO page_visits (page_url, visited_at) VALUES
					(?, ?)',
					$page->url(),
					new \DateTimeImmutable()
				);
			}
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