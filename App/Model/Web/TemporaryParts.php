<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Uri;
use Predis;

/**
 * Parts with low TTL
 */
final class TemporaryParts implements Parts {
	private const LIMIT = 5;
	private $redis;

	public function __construct(Predis\Client $redis) {
		$this->redis = $redis;
	}

	public function add(
		Part $part,
		Uri\Uri $uri,
		string $expression,
		string $language
	): void {
		if ($this->count() === self::LIMIT)
			$this->redis->del([new PartsName()]);
		$this->redis->hset(
			new PartsName(),
			new PartHash($uri, $expression, $language),
			serialize(
				[
					'content' => $part->content(),
					'url' => $uri->reference(),
					'expression' => $expression,
					'language' => $language,
				]
			)
		);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		foreach (array_map('unserialize', $this->redis->hgetall(new PartsName())) as $part)
			yield new ThrowawayPart(
				$this->redis,
				new Uri\ValidUrl($part['url']),
				$part['expression'],
				$part['language']
			);
	}

	public function count(): int {
		return $this->redis->hlen(new PartsName());
	}
}
