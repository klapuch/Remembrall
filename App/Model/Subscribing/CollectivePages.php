<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Storage;

/**
 * All pages stored in the database shared with everyone
 */
final class CollectivePages implements Pages {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function add(Page $page): Page {
		return (new Storage\Transaction($this->database))->start(
			function() use ($page) {
				$this->database->query(
					'INSERT INTO pages (url, content) VALUES
					(?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)',
					$page->url(),
					$page->content()->saveHTML()
				);
				$this->database->query(
					'INSERT INTO page_visits (page_id, visited_at) VALUES
					((SELECT ID FROM pages WHERE url = ?), ?)',
					$page->url(),
					new \DateTimeImmutable()
				);
				return $page;
			}
		);
	}

	public function replace(Page $old, Page $new) {
		(new Storage\Transaction($this->database))->start(
			function() use ($old, $new) {
				$this->database->query(
					'UPDATE pages SET content = ? WHERE url = ?',
					$new->content()->saveHTML(),
					$old->url()
				);
				$this->database->query(
					'UPDATE page_visits
					SET visited_at = ?
					WHERE page_id = (SELECT ID FROM pages WHERE url = ?)',
					new \DateTimeImmutable(),
					$old->url()
				);
			}
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