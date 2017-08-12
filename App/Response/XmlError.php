<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

/**
 * Error for XML format
 */
final class XmlError implements Application\Response {
	private const HEADERS = ['content-type' => 'text/xml; charset=utf8'];
	private const CODES = [400, 599],
		DEFAULT_CODE = 400,
		DELEGATE = 0;
	private $error;
	private $code;
	private $headers;

	public function __construct(
		\Throwable $error,
		array $headers = [],
		int $code = self::DELEGATE
	) {
		$this->error = $error;
		$this->code = $code;
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		$dom = new \DOMDocument('1.0', 'utf-8');
		$message = $dom->createElement('message');
		$text = $dom->createAttribute('text');
		$text->value = $this->text($this->error);
		$message->appendChild($text);
		$dom->appendChild($message);
		return new Output\DomFormat($dom, 'xml');
	}

	public function headers(): array {
		http_response_code($this->code($this->error, $this->code));
		return self::HEADERS + array_change_key_case($this->headers);
	}

	private function code(\Throwable $error, int $code): int {
		$choice = $error->getCode() ?: $code;
		return in_array($choice, range(...self::CODES))
			? $choice
			: self::DEFAULT_CODE;
	}

	private function text(\Throwable $error): string {
		return htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_XHTML)
			?: 'Unknown error, contact support.';
	}
}