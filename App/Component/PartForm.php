<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Dibi;
use GuzzleHttp;
use Nette\Application\UI;
use Nette\Caching\Storages;
use Nette\Forms;
use Nette\Utils\ArrayHash;
use Remembrall\Model\{
	Access, Http, Subscribing
};
use Tracy;

final class PartForm extends SecureControl {
	private $myself;
	private $database;
	private $logger;
	public $onSuccess = [];

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
		$this->template->setFile(__DIR__ . '/PartForm.latte');
		$this->template->render();
	}

	protected function createComponentForm() {
		$form = new UI\Form();
		$form->addText('url', 'URL')
			->addRule(UI\Form::FILLED)
			->addRule(UI\Form::URL, '%label is not valid');
		$form->addText('expression', 'XPath expression')
			->addRule(UI\Form::FILLED);
		$form->addText('start', 'Start interval')
			->addRule(UI\Form::FILLED)
			->addRule(
				function(Forms\IControl $control) {
					return date(
						'Y-m-d H:i',
						strtotime($control->getValue())
					) === $control->getValue();
				},
				'%label must be in format year-month-day hours:minutes'
			);
		$form->addInteger('interval', 'Interval')
			->addRule(UI\Form::FILLED)
			->addRule(UI\Form::MIN, 'Minimum range for %label is %d', 30);
		$form->addSubmit('act', 'Subscribe');
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	public function formSucceeded(UI\Form $form, ArrayHash $values) {
		try {
			$request = new Http\ConstantRequest(
				new Http\CaseSensitiveHeaders(
					new Http\UniqueHeaders(
						[
							'host' => $values['url'],
							'method' => 'GET',
							'http_errors' => false,
						]
					)
				)
			);
			$response = (new Http\WebBrowser(
				new GuzzleHttp\Client()
			))->send($request);
			$addedPage = (new Subscribing\LoggedPages(
				new Subscribing\CollectivePages($this->database),
				$this->logger
			))->add(
				new Subscribing\AvailableWebPage(
					new Subscribing\HtmlWebPage($request, $response),
					$response
				)
			);
			(new Subscribing\LoggedParts(
				new Subscribing\ReportedParts(
					new Subscribing\LimitedParts(
						$this->database,
						$this->myself,
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
			))->subscribe(
				new Subscribing\CachedPart(
					new Subscribing\HtmlPart(
						$addedPage,
						new Subscribing\ValidXPathExpression(
							new Subscribing\XPathExpression(
								$addedPage,
								$values['expression']
							)
						),
						$this->myself
					),
					new Storages\MemoryStorage()
				),
				new Subscribing\FutureInterval(
					new Subscribing\DateTimeInterval(
						new \DateTimeImmutable($values['start']),
						new \DateInterval(
							sprintf('PT%dM', max(0, $values['interval']))
						)
					)
				)
			);
			$this->onSuccess();
		} catch(\Throwable $ex) {
			$form->addError($ex->getMessage());
		}
	}
}
