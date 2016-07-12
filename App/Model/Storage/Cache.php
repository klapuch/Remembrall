<?php
declare(strict_types = 1);
namespace Remembrall\Model\Storage;

use Nette\Caching;

/**
 * Cache storage
 */
abstract class Cache {
	private $origin;
	private $cache;

	public function __construct($origin, Caching\IStorage $cache) {
		$this->origin = $origin;
		$this->cache = $cache;
	}

	/**
	 * Read cached $method if there is some
	 * If there is no values stored under the $method, cache it
	 * @param string $method
	 * @param array ...$args
	 * @return mixed|NULL
	 */
	public function read(string $method, ...$args) {
		$key = get_called_class() . '::' . $method . ($args ? md5(serialize($args)) : '');
		if($this->cache->read($key) === null)
			$this->cache->write($key, $this->origin->$method(...$args), []);
		return $this->cache->read($key);
	}
}