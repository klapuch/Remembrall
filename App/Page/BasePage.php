<?php
namespace Remembrall\Page;

use Klapuch\{
	Storage, Uri, Encryption, FlashMessage
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
		];
	}

	/**
	 * Flash message to the page
	 * @param string $content
	 * @param string $type
	 */
	final protected function flashMessage(string $content, string $type): void {
		(new FlashMessage\XmlMessage($_SESSION))->flash($content, $type);
	}

	/**
	 * Redirect relatively to the given url
	 * @param string $url
	 */
	final protected function redirect(string $url): void {
		header(sprintf('Location: %s', $this->url->reference() . $url));
		exit;
	}
}