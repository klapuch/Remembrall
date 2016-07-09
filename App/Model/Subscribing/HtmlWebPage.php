<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\Http;

/**
 * Fresh html page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $request;
	private $response;

	public function __construct(
		Http\Request $request,
		Http\Response $response
	) {
		$this->request = $request;
		$this->response = $response;
	}

	public function content(): \DOMDocument {
		if(!$this->isHTML())
			throw new Exception\ExistenceException('Web page must be HTML');
		$dom = new DOM();
		$dom->loadHTML($this->response->content());
		return $dom;
	}

	public function url(): string {
		return $this->request->headers()->header('host')->value();
	}

	public function equals(Page $page): bool {
		return $this->url() === $page->url();
	}

	/**
	 * Checks whether the page is HTML
	 * @return bool
	 */
	private function isHTML(): bool {
		return $this->response->headers()->included(
			new Http\CaseSensitiveHeader(
				'Content-Type',
				'text/html'
			)
		);
	}
}