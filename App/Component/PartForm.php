<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Klapuch\Storage;
use GuzzleHttp;
use Nette\Application\UI;
use Nette\Caching\Storages;
use Nette\Utils\ArrayHash;
use Remembrall\Model\{
	Access, Subscribing
};
use Tracy;

final class PartForm extends SecureControl {
	private $myself;
	private $database;
	private $logger;
	public $onSuccess = [];

	public function __construct(
		Access\Subscriber $myself,
		Storage\Database $database,
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
		$form->addInteger('interval', 'Interval')
			->addRule(UI\Form::FILLED)
			->addRule(UI\Form::MIN, 'Minimum range for %label is %d', 30);
		$form->addSubmit('act', 'Subscribe');
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	public function formSucceeded(UI\Form $form, ArrayHash $values) {
		try {
			$page = new Subscribing\LoggedPage(
				new Subscribing\CachedPage(
					$values->url,
					new Subscribing\HtmlWebPage(
						$values->url,
						new GuzzleHttp\Client(['http_errors' => false])
					),
					new Subscribing\WebPages($this->database),
					$this->database
				),
				$this->logger
			);
			(new Storage\PostgresTransaction($this->database))->start(
				function() use ($values, $page) {
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts(
							$this->database
						),
						$this->logger
					))->add(
						new Subscribing\CachedPart(
							new Subscribing\HtmlPart(
								new Subscribing\ValidXPathExpression(
									new Subscribing\XPathExpression(
										$page,
										$values->expression
									)
								),
								$page
							),
							new Storages\MemoryStorage()
						),
						$values->url,
						$values->expression
					);
					(new Subscribing\LoggedSubscriptions(
						new Subscribing\LimitedSubscriptions(
							$this->database,
							$this->myself,
							new Subscribing\OwnedSubscriptions(
								$this->myself,
								$this->database
							)
						),
						$this->logger
					))->subscribe(
						$values->url,
						$values->expression,
						new Subscribing\FutureInterval(
							new Subscribing\DateTimeInterval(
								new \DateTimeImmutable(),
								new \DateInterval(
									sprintf('PT%dM', max(0, $values->interval))
								)
							)
						)
					);
				}
			);
			$this->onSuccess();
		} catch(\Throwable $ex) {
			$form->addError($ex->getMessage());
		}
	}
}
