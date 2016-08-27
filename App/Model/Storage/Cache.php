<?php
declare(strict_types = 1);
namespace Remembrall\Model\Storage;

use Nette\Caching;

//TODO: Move to separate class

/**
 * Cache storage
 */
abstract class Cache {
	private $cache;
	private $expiration;
	protected $origin;

	public function __construct(
		$origin,
		Caching\IStorage $cache,
		\DateInterval $expiration
	) {
		$this->origin = $origin;
		$this->cache = $cache;
		$this->expiration = $expiration;
	}

	/**
	 * Read cached $method if there is some
	 * If there are no values stored under the $method and $identifier, cache it
	 * @param string $method
	 * @param string|null $identifier
	 * @param array ...$args
	 * @return mixed|NULL
	 */
	public function read(string $method, string $identifier = null, ...$args) {
		$key = sprintf(
			'%s::%s#%s-%s',
			get_called_class(),
			$method,
			($args ? md5(serialize($args)) : ''),
			($identifier ? md5($identifier) : '')
		);
		if($this->cache->read($key) === null) {
			$this->cache->write(
				$key,
				$this->origin->$method(...$args),
				[
					Caching\Cache::EXPIRE => sprintf(
						'%d seconds',
						$this->toSeconds($this->expiration)
					),
				]
			);
		}
		return $this->cache->read($key);
	}

	/**
	 * Converted expiration to single unit (seconds)
	 * @param \DateInterval $expiration
	 * @return int
	 */
	private function toSeconds(\DateInterval $expiration): int {
		return $expiration->days * 86400
		+ $expiration->h * 3600
		+ $expiration->i * 60
		+ $expiration->s;
	}
}
