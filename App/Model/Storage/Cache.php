<?php
declare(strict_types = 1);
namespace Remembrall\Model\Storage;

use Nette\Caching;

/**
 * Cache storage
 */
abstract class Cache {
	private $cache;
	protected $origin;

	public function __construct($origin, Caching\IStorage $cache) {
		$this->origin = $origin;
		$this->cache = $cache;
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
				$this->origin->$method(...$args)
			);
		}
		return $this->cache->read($key);
	}
}
