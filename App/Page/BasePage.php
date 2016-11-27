<?php
namespace Remembrall\Page;

use Klapuch\{
	Storage, Uri, Encryption, FlashMessage, Csrf, Output, Form
};
use Remembrall\Model\Access;
use Tracy;

abstract class BasePage {
	protected const TEMPLATES = __DIR__ . '/templates';
	/** @var Uri\BaseUrl */
	private $url;
	/** @var \Remembrall\Model\Access\Subscriber */
	protected $subscriber;
	/** @var \Klapuch\Storage\Database */
	protected $database;
	/** @var \Tracy\ILogger */
	protected $logger;
	/** @var \Klapuch\Encryption\Cipher */
	protected $cipher;
	/** @var \Klapuch\Csrf\Csrf */
	protected $csrf;
	/** @var \Klapuch\Form\Backup */
	protected $backup;

	public function __construct(
		Uri\Uri $url,
		Storage\Database $database,
		Tracy\Logger $logger,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->logger = $logger;
		$this->url = $url;
		$this->cipher = $cipher;
		$this->csrf = new Csrf\StoredCsrf($_SESSION, $_POST, $_GET);
		$this->backup = new Form\Backup($_SESSION);
	}

	public function startup() {
		$this->subscriber = new Access\FakeSubscriber(0, 'NoOne');
		if(isset($_SESSION['id'])) {
			$this->subscriber = new Access\RegisteredSubscriber(
				$_SESSION['id'],
				$this->database
			);
		}
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
}
