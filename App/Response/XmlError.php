<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

final class XmlError implements Application\Response {
	private const HEADERS = ['content-type' => 'text/xml; charset=utf8'];
	private const CODES = [400, 599],
		BAD_REQUEST = 400;
	private $text;
	private $code;
	private $headers;

	public function __construct(
		string $text,
		int $code = self::BAD_REQUEST,
		array $headers = []
	) {
		$this->text = $text;
		$this->code = $code;
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		$dom = new \DOMDocument('1.0', 'utf-8');
		$message = $dom->createElement('message');
		$text = $dom->createAttribute('text');
		$text->value = htmlspecialchars($this->text, ENT_QUOTES | ENT_XHTML);
		$message->appendChild($text);
		$dom->appendChild($message);
		return new Output\DomFormat($dom, 'xml');
	}

	public function headers(): array {
		http_response_code(
			in_array($this->code, range(...self::CODES))
				? $this->code
				: self::BAD_REQUEST
		);
		return self::HEADERS + array_change_key_case($this->headers);
	}
}