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
			$page = (new Http\LoggedRequest(
				new Http\CachedRequest(
					new Http\FrugalRequest(
						new Http\DefaultRequest(
							new GuzzleHttp\Client(['http_errors' => false]),
							new Http\CaseSensitiveHeaders(
								new Http\UniqueHeaders(
									[
										'host' => $values['url'],
										'method' => 'GET',
									]
								)
							)
						),
						new Http\CaseSensitiveHeaders(
							new Http\UniqueHeaders(['host' => $values['url']])
						),
						new Subscribing\WebPages($this->database),
						$this->database
					),
					new Storages\MemoryStorage()
				),
				$this->logger
			))->send();
			(new Subscribing\LoggedParts(
				new Subscribing\LimitedParts(
					$this->database,
					$this->myself,
					new Subscribing\OwnedParts(
						new Subscribing\CollectiveParts(
							$this->database
						),
						$this->database,
						$this->myself
					)
				),
				$this->logger
			))->subscribe(
				new Subscribing\CachedPart(
					new Subscribing\HtmlPart(
						new Subscribing\ValidXPathExpression(
							new Subscribing\XPathExpression(
								$page,
								$values['expression']
							)
						),
						$page
					),
					new Storages\MemoryStorage()
				),
				$values['url'],
				$values['expression'],
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
