<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Csrf;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

abstract class Layout implements Application\Request {
	/** @var mixed[] */
	protected $configuration;
	/** @var \Klapuch\Uri\Uri */
	protected $url;
	/** @var \Klapuch\Log\Logs */
	protected $logs;
	/** @var \Klapuch\Csrf\Csrf */
	protected $csrf;
	/** @var \Klapuch\Access\User */
	protected $user;
	/** @var \PDO */
	protected $database;

	public function __construct(
		Uri\Uri $url,
		Log\Logs $logs,
		Ini\Ini $configuration
	) {
		$this->url = $url;
		$this->logs = $logs;
		$this->configuration = $configuration->read();
		$this->database = new Storage\SafePDO(
			$this->configuration['DATABASE']['dsn'],
			$this->configuration['DATABASE']['user'],
			$this->configuration['DATABASE']['password']
		);
		$this->csrf = new Csrf\StoredCsrf($_SESSION, $_POST, $_GET);
		$this->user = new Access\Guest();
		if (isset($_SESSION['id'])) {
			$this->user = new Access\CachedUser(
				new Access\RegisteredUser($_SESSION['id'], $this->database)
			);
		}
	}

	/**
	 * Flash message to the page
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	final protected function flashMessage(string $content, string $type): void {
		(new UI\PersistentFlashMessage($_SESSION))->flash($content, $type);
	}

	/**
	 * Redirect relatively to the given url
	 * @param string $url
	 * @return void
	 */
	final protected function redirect(string $url): void {
		header(sprintf('Location: %s/%s', $this->url->reference(), $url));
		exit;
	}

	/**
	 * Protect against CSRF
	 * @throws \Exception
	 */
	final protected function protect(): void {
		if ($this->csrf->abused())
			throw new \Exception('Timeout');
	}

	/**
	 * Log the exception
	 * @param \Throwable $ex
	 * @return void
	 */
	final protected function log(\Throwable $ex): void {
		$this->logs->put(
			new Log\PrettyLog(
				$ex,
				new Log\PrettySeverity(
					new Log\JustifiedSeverity(Log\Severity::ERROR)
				)
			)
		);
	}
}