<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
    Output, Uri
};

/**
 * Constant part without roundtrips
 */
final class ConstantPart implements Part {
	private $origin;
	private $content;
	private $url;

	public function __construct(
		Part $origin,
		string $content,
		string $url
	) {
		$this->origin = $origin;
		$this->content = $content;
		$this->url = $url;
	}

	public function content(): string {
		return $this->content;
	}

	public function refresh(): Part {
		return $this->origin->refresh();
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->with('url', $this->url->reference());
	}
}
