<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Http;

/**
 * HTML web page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $request;

	public function __construct(Http\Request $request) {
		$this->request = $request;
	}

	public function content(): \DOMDocument {
		$response = new Http\ExplainedResponse(
			new Http\StrictResponse(
				['Content-Type' => 'text/html'],
				new Http\AvailableResponse(
					(new Http\ExplainedRequest(
						$this->request,
						'Error during requesting the page.'
					))->send()
				)
			),
			'Page must be available HTML page.'
		);
		$dom = new DOM();
		$dom->loadHTML($response->body());
		return $dom;
	}

	public function refresh(): Page {
		return new self($this->request);
	}
}