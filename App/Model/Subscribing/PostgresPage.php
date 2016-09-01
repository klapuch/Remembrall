<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri
};

/**
 * Page stored in the PostgreSQL database
 */
final class PostgresPage implements Page {
    private $origin;
    private $uri;
    private $database;

    public function __construct(
        Page $origin,
		Uri\Uri $uri,
        Storage\Database $database
    ) {
        $this->origin = $origin;
        $this->uri = $uri;
        $this->database = $database;
    }

    public function content(): \DOMDocument {
        $content = new DOM();
        $content->loadHTML(
            $this->database->fetchColumn(
                'SELECT content
                FROM pages
                WHERE url IS NOT DISTINCT FROM ?',
                [$this->uri->reference()]
            )
        );
        return $content;
    }

    public function refresh(): Page {
        $refreshedPage = $this->origin->refresh();
        $this->database->query(
            'UPDATE pages
            SET content = ?
            WHERE url IS NOT DISTINCT FROM ?',
            [$refreshedPage->content()->saveHTML(), $this->uri->reference()]
        );
        return $this;
    }
}
