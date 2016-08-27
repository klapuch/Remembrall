<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

/**
 * Page stored in the PostgreSQL database
 */
final class PostgresPage implements Page {
    private $origin;
    private $url;
    private $database;

    public function __construct(
        Page $origin,
        string $url,
        Storage\Database $database
    ) {
        $this->origin = $origin;
        $this->url = $url;
        $this->database = $database;
    }

    public function content(): \DOMDocument {
        $content = new DOM();
        $content->loadHTML(
            $this->database->fetchColumn(
                'SELECT content
                FROM pages
                WHERE url IS NOT DISTINCT FROM ?',
                [$this->url]
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
            [$refreshedPage->content()->saveHTML(), $this->url]
        );
        return $this;
    }
}
