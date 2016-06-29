<?php
namespace Remembrall\Page;

use GuzzleHttp;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Http, Subscribing
};

final class PartsPage extends BasePage {
	public function createComponentPartForm() {
		$form = new Form();
		$form->addText('url', 'URL');
		$form->addText('expression', 'XPath expression');
		$form->addText('start', 'Start');
		$form->addText('interval', 'Interval');
		$form->addSubmit('act', 'Act');
		$form->onSuccess[] = function(Form $form, ArrayHash $values) {
			$this->succeededPartForm($form, $values);
		};
		return $form;
	}

	public function succeededPartForm(Form $form, ArrayHash $values) {
		try {
			$request = new Http\ConstantRequest(
				new Http\CaseSensitiveHeaders(
					new Http\UniqueHeaders(
						['host' => $values['url'], 'method' => 'GET']
					)
				)
			);
			$response = (new Http\WebBrowser(new GuzzleHttp\Client()))
				->send($request);
			$addedPage = (new Subscribing\MySqlPages($this->database))->add(
				new Subscribing\AvailableWebPage(
					new Subscribing\HtmlWebPage(
						$request, $response
					),
					$response
				)
			);
			(new Subscribing\LimitedParts(
				$this->database,
				new Access\MySqlSubscriber(
					$this->user->getId(),
					$this->database
				),
				new Subscribing\OwnedParts(
					$this->database,
					new Access\MySqlSubscriber(
						$this->user->getId(),
						$this->database
					),
					new Subscribing\CollectiveParts($this->database)
				)
			))->subscribe(
				new Subscribing\HtmlPart(
					$addedPage,
					new Subscribing\ValidXPathExpression(
						new Subscribing\XPathExpression(
							$addedPage,
							$values['expression']
						)
					),
					new Access\MySqlSubscriber(
						$this->user->getId(),
						$this->database
					)
				),
				new Subscribing\FutureInterval(
					new Subscribing\DateTimeInterval(
						new \DateTimeImmutable($values['start']),
						new \DateInterval(sprintf('PT%dM', $values['interval']))
					)
				)
			);
		} catch(\Exception $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		}
	}

	public function renderDefault() {
	}
}
