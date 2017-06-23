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
	private const TTL = '30 minutes',
		ACCESS = 'parts_access';
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
		$this->redis->transaction(function() use ($uri, $expression, $language, $part): void {
			$this->redis->hdel(new PartsName(), $this->remainders());
			$this->redis->hdel(self::ACCESS, $this->remainders());
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
			$this->redis->hset(
				self::ACCESS,
				new PartHash($uri, $expression, $language),
				time()
			);
		});
	}

	public function all(Dataset\Selection $selection): \Traversable {
		foreach (array_map('unserialize', $this->redis->hgetall(new PartsName())) as $part) {
			yield new TemporaryPart(
				$this->redis,
				new Uri\ValidUrl($part['url']),
				$part['expression'],
				$part['language']
			);
		}
	}

	public function count(): int {
		return $this->redis->hlen(new PartsName());
	}

	/**
	 * All the old parts which are needed to delete
	 * @return array
	 */
	private function remainders(): array {
		return array_pad(
			array_keys(
				array_filter(
					$this->redis->hgetall(self::ACCESS),
					[$this, 'expired']
				)
			),
			1,
			0
		);
	}

	/**
	 * Is time too old?
	 * @param int $time
	 * @return bool
	 */
	private function expired(int $time): bool {
		return $time < strtotime(sprintf('-%s', self::TTL));
	}
}
