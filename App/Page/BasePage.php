<?php
namespace Remembrall\Page;

use Klapuch\{
	Storage, Uri
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
	public $database;
	/** @var \Tracy\ILogger */
	public $logger;

	public function __construct(
		Uri\Uri $url,
		Storage\Database $database,
		Tracy\Logger $logger
	) {
		$this->database = $database;
		$this->logger = $logger;
		$this->subscriber = new Access\RegisteredSubscriber(1, $database);
		$this->url = $url;
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