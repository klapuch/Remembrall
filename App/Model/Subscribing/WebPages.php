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

	public function add(string $url, Page $page): Page {
		(new Storage\Transaction($this->database))->start(function() use($url, $page) {
			if($this->alreadyExists($url)) {
				$this->database->query(
					'UPDATE pages SET content = ? WHERE url = ?',
					$page->content()->saveHTML(),
					$this->normalizedUrl($url)
				);
			} else {
				$this->database->query(
					'INSERT INTO pages (url, content) VALUES
					(?, ?)',
					$this->normalizedUrl($url),
					$page->content()->saveHTML()
				);
			}
			$this->database->query(
				'INSERT INTO page_visits (page_url, visited_at) VALUES
				(?, ?)',
				$this->normalizedUrl($url),
				new \DateTimeImmutable()
			);
		});
		return $page;
	}

	/**
	 * Does the url already exists
	 * @param string $url
	 * @return bool
	 */
	private function alreadyExists(string $url): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1 FROM pages WHERE url = ?',
			$this->normalizedUrl($url)
		);
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

	private function normalizedUrl(string $url): string {
		$parsedUrl = parse_url(strtolower(trim($url, '/')));
		$scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
		$host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
		$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
		$query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
		$fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
		return $scheme . $host . $path . $query . $fragment;
	}
}