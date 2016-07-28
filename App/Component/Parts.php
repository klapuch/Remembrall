<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Dibi;
use Remembrall\Model\{
	Access, Subscribing
};
use Tracy;

final class Parts extends SecureControl {
	private $myself;
	private $database;
	private $logger;

	public function __construct(
		Access\Subscriber $myself,
		Dibi\Connection $database,
		Tracy\ILogger $logger
	) {
		parent::__construct();
		$this->myself = $myself;
		$this->database = $database;
		$this->logger = $logger;
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Parts.latte');
		$this->template->parts = (new Subscribing\OwnedSubscriptions(
			$this->myself,
			$this->database
		))->iterate();
		$this->template->render();
	}

	public function handleCancel(string $url, string $expression) {
		try {
			(new Subscribing\LoggedSubscription(
				new Subscribing\OwnedSubscription(
					$url,
					$expression,
					$this->myself,
					$this->database
				),
				$this->logger
			))->cancel();
			if(!$this->presenter->isAjax()) {
				$this->presenter->flashMessage(
					'Part has been deleted',
					'success'
				);
				$this->presenter->redirect('this');
			}
		} catch(\Throwable $ex) {
			$this->presenter->flashMessage($ex->getMessage(), 'danger');
			$this->presenter->redirect('this');
		}
	}
}
