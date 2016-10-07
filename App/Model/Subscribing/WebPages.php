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

	public function add(Uri\Uri $url, Page $page): Page {
		$this->database->query(
			'INSERT INTO pages (url, content)
			VALUES (:url, :content)
			ON CONFLICT (url) DO UPDATE
			SET content = :content',
			[
				':url' => $url->reference(),
				':content' => $page->content()->saveHTML(),
			]
		);
		return $page;
    }
}