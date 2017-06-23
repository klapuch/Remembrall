<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Output;
use Klapuch\Uri;
use Predis;

/**
 * Part with low TTL
 */
final class TemporaryPart implements Part {
	private $redis;
	private $url;
	private $expression;
	private $language;

	public function __construct(
		Predis\Client $redis,
		Uri\Uri $url,
		string $expression,
		string $language
	) {
		$this->redis = $redis;
		$this->url = $url;
		$this->expression = $expression;
		$this->language = $language;
	}

	public function content(): string {
		throw new \Exception('Not implemented');
	}

	public function snapshot(): string {
		throw new \Exception('Not implemented');
	}

	public function refresh(): Part {
		throw new \Exception('Not implemented');
	}

	public function print(Output\Format $format): Output\Format {
		$key = new PartHash($this->url, $this->expression, $this->language);
		if ($this->redis->hexists(new PartsName(), $key)) {
			return new Output\FilledFormat(
				$format,
				unserialize($this->redis->hget(new PartsName(), $key))
			);
		}
		throw new \UnexpectedValueException(
			'Part not found',
			0,
			new \Exception(
				sprintf(
					'Part for "%s" URL and %s expression "%s" not found',
					$this->url->reference(),
					$this->language,
					$this->expression
				)
			)
		);
	}
}