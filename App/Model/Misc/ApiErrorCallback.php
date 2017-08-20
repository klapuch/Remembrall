<?php
declare(strict_types = 1);
namespace Remembrall\Model\Misc;

/**
 * Callback with assurance that every potential error will be transformed to HTTP status code suited (REST) API
 */
final class ApiErrorCallback implements Callback {
	private $code;

	public function __construct(int $code) {
		$this->code = $code;
	}

	/**
	 * @return mixed
	 */
	public function invoke(callable $callback, array $args = []) {
		try {
			return call_user_func_array($callback, $args);
		} catch (\Throwable $ex) {
			throw new $ex(
				$ex->getMessage(),
				$this->code,
				$ex
			);
		}
	}
}