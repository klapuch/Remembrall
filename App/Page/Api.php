<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Storage;
use Klapuch\Uri;

abstract class Api implements Application\View {
	/** @var mixed[] */
	protected $configuration;
	/** @var \Klapuch\Uri\Uri */
	protected $url;
	/** @var \Klapuch\Log\Logs */
	protected $logs;
	/** @var \Klapuch\Access\User */
	protected $user;
	/** @var \PDO */
	protected $database;

	public function __construct(
		Uri\Uri $url,
		Log\Logs $logs,
		Ini\Source $configuration
	) {
		$this->url = $url;
		$this->logs = $logs;
		$this->configuration = $configuration->read();
		$this->database = new Storage\SafePDO(
			$this->configuration['DATABASE']['dsn'],
			$this->configuration['DATABASE']['user'],
			$this->configuration['DATABASE']['password']
		);
		$this->user = new Access\Guest();
		if (isset($_SESSION['id'])) {
			$this->user = new Access\CachedUser(
				new Access\RegisteredUser($_SESSION['id'], $this->database)
			);
		}
	}
}