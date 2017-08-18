<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Csrf;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Storage;
use Klapuch\Uri;
use Predis;

abstract class Layout implements Application\View {
	/** @var mixed[] */
	protected $configuration;
	/** @var \Klapuch\Uri\Uri */
	protected $url;
	/** @var \Klapuch\Log\Logs */
	protected $logs;
	/** @var \Klapuch\Csrf\Protection */
	protected $csrf;
	/** @var \Klapuch\Access\User */
	protected $user;
	/** @var \PDO */
	protected $database;
	/** @var \Predis\Client */
	protected $redis;

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
		$this->redis = new Predis\Client($this->configuration['REDIS']['uri']);
		$this->csrf = new Csrf\Memory($_SESSION, $_POST, $_GET);
		$this->user = (new Access\WebEntrance(
			$this->database
		))->enter($_SESSION);
	}
}