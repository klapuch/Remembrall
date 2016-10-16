<?php
namespace Remembrall\Page;

use Klapuch\{
	Storage, Uri
};
use Remembrall\Model\Access;
use Tracy;

abstract class BasePage {
	protected const TEMPLATES = __DIR__ . '/templates';
	/** @var \Klapuch\Storage\Database */
	public $database;
	/** @var \Tracy\ILogger */
	public $logger;
	/** @var Uri\BaseUrl */
	protected $url;
	/** @var \Remembrall\Model\Access\Subscriber */
	protected $subscriber;

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
}