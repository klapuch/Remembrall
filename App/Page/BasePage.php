<?php
namespace Remembrall\Page;

use Klapuch\{
	Storage, Uri, Encryption
};
use Remembrall\Model\Access;
use Tracy;

abstract class BasePage {
	protected const TEMPLATES = __DIR__ . '/templates';
	/** @var Uri\BaseUrl */
	protected $url;
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
		$this->subscriber = new Access\FakeSubscriber(1, 'foo@bar.cz');
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
	protected function layout(): array {
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
		];
	}
}