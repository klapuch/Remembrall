<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Dibi;
use GuzzleHttp;
use Nette\Caching\Storages;
use Remembrall\Model\{
	Access, Http, Subscribing
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
		$this->myself = $myself;
		$this->database = $database;
		$this->logger = $logger;
		parent::__construct();
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Parts.latte');
		$this->template->parts = (new Subscribing\OwnedParts(
			$this->database,
			$this->myself
		))->iterate();
		$this->template->render();
	}

	public function handleRemove(string $url, string $expression) {
		try {
			(new Subscribing\LoggedParts(
				new Subscribing\OwnedParts(
					$this->database,
					$this->myself
				),
				$this->logger
			))->remove(
				new Subscribing\OwnedPart(
					$this->database,
					new Subscribing\FakeExpression($expression),
					$this->myself,
					new Subscribing\FakePage($url)
				)
			);
			if(!$this->presenter->isAjax()) {
				$this->presenter->flashMessage(
					'Part has been deleted',
					'success'
				);
				$this->presenter->redirect('this');
			}
			$this->redrawControl();
		} catch(\Throwable $ex) {
			$this->presenter->flashMessage($ex->getMessage(), 'danger');
			$this->presenter->redirect('this');
		}
	}

	public function handleRefresh(string $url, string $expression) {
		try {
			$page = (new Http\LoggingBrowser(
				new Http\CachingBrowser(
					new Http\WebBrowser(
						new GuzzleHttp\Client(), $this->database
					),
					$this->database
				),
				$this->logger
			))->send(
				new Http\ConstantRequest(
					new Http\CaseSensitiveHeaders(
						new Http\UniqueHeaders(
							[
								'host' => $url,
								'method' => 'GET',
								'http_errors' => false,
							]
						)
					)
				)
			);
			(new Subscribing\LoggedParts(
				new Subscribing\ReportedParts(
					new Subscribing\ChangedParts(
						new Subscribing\OwnedParts(
							$this->database,
							$this->myself
						)
					),
					new Subscribing\LoggedReports(
						new Subscribing\OwnedReports(
							$this->myself, $this->database
						),
						$this->logger
					)
				),
				$this->logger
			))->replace(
				new Subscribing\OwnedPart(
					$this->database,
					new Subscribing\FakeExpression($expression),
					$this->myself,
					new Subscribing\FakePage($url)
				),
				new Subscribing\CachedPart(
					new Subscribing\HtmlPart(
						$page,
						new Subscribing\ValidXPathExpression(
							new Subscribing\XPathExpression($page, $expression)
						),
						$this->myself
					),
					new Storages\MemoryStorage()
				)
			);
			$this->presenter->flashMessage(
				'The part has been refreshed',
				'success'
			);
		} catch(\Throwable $ex) {
			$this->presenter->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->presenter->redirect('this');
		}
	}
}
