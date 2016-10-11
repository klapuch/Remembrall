<?php
namespace Remembrall\Page;

use Klapuch\Uri;
use Nette;
use Nette\Security;

abstract class BasePage extends Nette\Application\UI\Presenter {
	use \Nextras\Application\UI\SecuredLinksPresenterTrait;
	protected const TEMPLATES = __DIR__ . '/templates';
	/** @inject @var \Klapuch\Storage\Database */
	public $database;
	/** @inject @var \Tracy\ILogger */
	public $logger;
	/** @inject @var \Remembrall\Model\Access\Subscriber */
	public $subscriber;
	/** @var Uri\BaseUrl */
	protected $baseUrl;

	public function __construct() {
		parent::__construct();
		$this->baseUrl = new Uri\BaseUrl(
			$_SERVER['SCRIPT_NAME'],
			$_SERVER['REQUEST_URI']
		);
	}

	public function startup() {
		parent::startup();
		$this->user->login(new Security\Identity(1));
	}

	protected function createTemplate() {
		// Do not call createTemplate
	}
}