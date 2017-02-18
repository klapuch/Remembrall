<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Http;

/**
 * HTML web page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private const CONTENT_TYPE = 'text/html';
	private $request;

	public function __construct(Http\Request $request) {
		$this->request = $request;
	}

	public function content(): \DOMDocument {
		try {
			$response = new Http\StrictResponse(
				['Content-Type' => self::CONTENT_TYPE],
				new Http\AvailableResponse($this->request->send())
			);
			$dom = new DOM();
			$dom->loadHTML($response->body());
			return $dom;
		} catch(\Throwable $ex) {
			throw new \Remembrall\Exception\NotFoundException(
				'Page is unreachable. Does the URL exist?',
				$ex->getCode(),
				$ex
			);
		}
	}

	public function refresh(): Page {
		return new self($this->request);
	}
}