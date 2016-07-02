<?php
declare(strict_types = 1);
namespace Remembrall\Model\Storage;

use Dibi;

/**
 * Single transaction for Dibi
 */
final class Transaction {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	/**
	 * Start the transaction with proper commit/rollback
	 * And rethrowing an exception in case error occur
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	public function start(\Closure $callback) {
		$this->database->begin();
		try {
			$result = $callback();
			$this->database->commit();
			return $result;
		} catch(\Throwable $ex) {
			$this->database->rollback();
			if(get_class($ex) === Dibi\DriverException::class) {
				throw new \RuntimeException(
					'Error on the database side. Rolled back.',
					(int)$ex->getCode(),
					$ex
				);
			}
			throw $ex;
		}
	}
}