<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Uri, Encryption, FlashMessage, Csrf, Form, Log, Output
};
use Remembrall\Model\Access;

abstract class BasePage {
	private const TEMPLATES = __DIR__ . '/templates';
	/** @var \Klapuch\Uri\Uri */
	protected $url;
	/** @var \Remembrall\Model\Access\Subscriber */
	protected $subscriber;
	/** @var \PDO */
	protected $database;
	/** @var \Klapuch\Log\Logs */
	protected $logs;
	/** @var \Klapuch\Encryption\Cipher */
	protected $cipher;
	/** @var \Klapuch\Csrf\Csrf */
	protected $csrf;
	/** @var \Klapuch\Form\Storage */
	protected $storage;

	public function __construct(
		Uri\Uri $url,
		\PDO $database,
		Log\Logs $logs,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->logs = $logs;
		$this->url = $url;
		$this->cipher = $cipher;
		$this->csrf = new Csrf\StoredCsrf($_SESSION, $_POST, $_GET);
		$this->storage = new Form\Storage($_SESSION, $_POST);
	}

	public function startup(): void {
		$this->subscriber = new Access\FakeSubscriber(0, 'NoOne');
		if(isset($_SESSION['id'])) {
			$this->subscriber = new Access\RegisteredSubscriber(
				$_SESSION['id'],
				$this->database
			);
		}
	}

	/**
	 * Render the template
	 * @param string $page
	 * @param string $action
	 * @param array $parameters
	 * @return string
	 */
	final public function render(
		string $page,
		string $action,
		array $parameters
	): string {
		$method = 'render' . $action;
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . sprintf('/%s/%s.xml', $page, $action));
		return (new Output\XsltTemplate(
			self::TEMPLATES . sprintf('/%s/%s.xsl', $page, $action),
			new Output\MergedXml(
				$xml,
				$this->$method($parameters),
				...$this->layout()
			)
		))->render();
	}

	/**
	 * XML for layout
	 * @return array
	 */
	final protected function layout(): array {
		return [
			new \SimpleXMLElement(
				sprintf(
					'<subscriber><email>%s</email><id>%d</id></subscriber>',
					$this->subscriber->email(),
					$this->subscriber->id()
				)
			),
			new \SimpleXMLElement(
				sprintf('<baseUrl>%s</baseUrl>', $this->url->reference())
			),
			new \SimpleXMLElement(
				(new FlashMessage\XmlMessage($_SESSION))->print()
			),
			new \SimpleXMLElement(
				sprintf(
					'<csrf><link>%s</link><input>%s</input></csrf>',
					(new Csrf\CsrfLink($this->csrf))->protection(),
					(new Csrf\CsrfInput($this->csrf))->protection()
				)
			),
		];
	}

	/**
	 * Flash message to the page
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	final protected function flashMessage(string $content, string $type): void {
		(new FlashMessage\XmlMessage($_SESSION))->flash($content, $type);
	}

	/**
	 * Redirect relatively to the given url
	 * @param string $url
	 * @return void
	 */
	final protected function redirect(string $url): void {
		header(sprintf('Location: %s', $this->url->reference() . $url));
		exit;
	}

	/**
	 * Protect against CSRF
	 * @throws \Exception
	 */
	final protected function protect(): void {
		if($this->csrf->abused())
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