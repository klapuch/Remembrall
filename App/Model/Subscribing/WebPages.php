<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri
};

final class WebPages implements Pages {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function add(Uri\Uri $uri, Page $page): Page {
        if(!$this->alreadyExists($uri)) {
            $this->database->query(
                'INSERT INTO pages (url, content) VALUES
                (?, ?)',
                [$uri->reference(), $page->content()->saveHTML()]
            );
        }
        return $page;
	}

	/**
	 * Does the url already exist?
	 * @param Uri\Uri $uri
	 * @return bool
	 */
	private function alreadyExists(Uri\Uri $uri): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			[$uri->reference()]
		);
	}
}
