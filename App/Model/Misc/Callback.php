<?php
declare(strict_types = 1);
namespace Remembrall\Model\Misc;

interface Callback {
	/**
	 * Invoke the given callback
	 * @param callable $callback
	 * @param array ...$args
	 * @return mixed
	 */
	public function invoke(callable $callback, ...$args);
}