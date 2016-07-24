<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\Http;

/**
 * Fresh html page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $response;
	private $request;

	public function __construct(
		Http\Response $response,
		Http\Request $request
	) {
		$this->response = $response;
		$this->request = $request;
	}

	public function content(): \DOMDocument {
		$dom = new DOM();
		$dom->loadHTML($this->response->content());
		return $dom;
	}

	public function refresh(): Page {
		return $this->request->send();
	}
}