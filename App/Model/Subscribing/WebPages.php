<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

final class WebPages implements Pages {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function add(string $url, Page $page): Page {
		(new Storage\PostgresTransaction($this->database))->start(
			function() use ($url, $page) {
				if($this->alreadyExists($url)) {
					$this->database->query(
						'UPDATE pages
						SET content = ?
						WHERE url IS NOT DISTINCT FROM ?',
						[$page->content()->saveHTML(), $this->normalizedUrl($url)]
					);
				} else {
					$this->database->query(
						'INSERT INTO pages (url, content) VALUES
						(?, ?)',
						[$this->normalizedUrl($url), $page->content()->saveHTML()]
					);
				}
			}
		);
		return $page;
	}

	/**
	 * Does the url already exists
	 * @param string $url
	 * @return bool
	 */
	private function alreadyExists(string $url): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			[$this->normalizedUrl($url)]
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll('SELECT content FROM pages'),
			function($previous, array $row) {
				$previous[] = new ConstantPage(new FakePage(), $row['content']);
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