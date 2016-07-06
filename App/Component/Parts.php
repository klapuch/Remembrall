<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Dibi;
use GuzzleHttp;
use Nette\Caching\Storages;
use Nette\Security;
use Remembrall\Model\{
	Access, Http, Subscribing
};

final class Parts extends SecureControl {
	private $parts;
	private $myself;
	private $database;

	public function __construct(
		Subscribing\Parts $parts,
		Access\Subscriber $myself,
		Dibi\Connection $database
	) {
		$this->parts = $parts;
		$this->myself = $myself;
		$this->database = $database;
		parent::__construct();
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Parts.latte');
		$this->template->parts = $this->parts->iterate();
		$this->template->render();
	}

	public function handleRemove(string $url, string $expression) {
		try {
			$this->parts->remove(
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
			$request = new Http\ConstantRequest(
				new Http\CaseSensitiveHeaders(
					new Http\UniqueHeaders(
						[
							'host' => $url,
							'method' => 'GET',
							'http_errors' => false,
						]
					)
				)
			);
			$page = new Subscribing\HtmlWebPage(
				$request,
				(new Http\WebBrowser(new GuzzleHttp\Client()))->send($request)
			);
			$this->parts->replace(
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
			$this->presenter->flashMessage('The part has been refreshed', 'success');
		} catch(\Throwable $ex) {
			$this->presenter->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->presenter->redirect('this');
		}
	}
}
