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
		parent::__construct();
		$this->myself = $myself;
		$this->database = $database;
		$this->logger = $logger;
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Parts.latte');
		$this->template->parts = (new Subscribing\OwnedParts(
			new Subscribing\CollectiveParts(
				$this->database,
				new Http\FakeBrowser()
			),
			$this->database,
			$this->myself,
			new Http\FakeBrowser()
		))->iterate();
		$this->template->render();
	}

	public function handleRemove(string $url, string $expression) {
		try {
			(new Subscribing\LoggedParts(
				new Subscribing\OwnedParts(
					new Subscribing\CollectiveParts(
						$this->database,
						new Http\FakeBrowser()
					),
					$this->database,
					$this->myself,
					new Http\FakeBrowser()
				),
				$this->logger
			))->remove($url, $expression);
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
			$browser = new Http\LoggingBrowser(
				new Http\CachingBrowser(
					new Http\WebBrowser(
						new GuzzleHttp\Client(['http_errors' => false]),
						new Subscribing\WebPages($this->database),
						$this->database
					),
					$this->database
				),
				$this->logger
			);
			$page = $browser->send(
				new Http\ConstantRequest(
					new Http\CaseSensitiveHeaders(
						new Http\UniqueHeaders(
							[
								'host' => $url,
								'method' => 'GET',
							]
						)
					)
				)
			);
			(new Subscribing\LoggedParts(
				new Subscribing\ChangedParts(
					new Subscribing\CollectiveParts($this->database, $browser),
					$browser
				),
				$this->logger
			))->subscribe(
				new Subscribing\OwnedPart(
					new Subscribing\HtmlPart(
						new Subscribing\ValidXPathExpression(
							new Subscribing\XPathExpression($page, $expression)
						),
						$browser,
						$page
					),
					$page,
					new Subscribing\XPathExpression($page, $expression),
					$this->database,
					$this->myself
				),
				$url,
				$expression,
				new Subscribing\FakeInterval(new \DateTimeImmutable())
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
