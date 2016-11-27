<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Form, Http, Output, Storage, Time, Uri
};
use Nette\Caching\Storages;
use Remembrall\Model\Subscribing;

final class SubscriptionPage extends BasePage {
	private const COLUMNS = 5;

	public function renderDefault() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Subscription/default.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Subscription/default.xsl',
			new Output\MergedXml(
				$xml,
				new \SimpleXMLElement(
					sprintf(
						'<forms><subscribing>%s</subscribing></forms>',
						(new Form\RawForm(
							['method' => 'POST', 'action' => 'subscribe', 'role' => 'form', 'class' => 'form-horizontal'],
							new Form\CsrfInput($_SESSION, $_POST),
							new Form\BootstrapInput(
								new Form\BoundControl(
									new Form\SafeInput([
										'type' => 'text',
										'name' => 'url',
										'class' => 'form-control',
										'required' => 'required',
									], $this->backup),
									new Form\LinkedLabel('Url', 'url')
								),
								self::COLUMNS
							),
							new Form\BootstrapInput(
								new Form\BoundControl(
									new Form\SafeInput([
										'type' => 'text',
										'name' => 'expression',
										'class' => 'form-control',
										'required' => 'required',
									], $this->backup),
									new Form\LinkedLabel('Expression', 'expression')
								),
								self::COLUMNS
							),
							new Form\BootstrapInput(
								new Form\BoundControl(
									new Form\SafeInput([
										'type' => 'number',
										'name' => 'interval',
										'class' => 'form-control',
										'min' => '30',
										'required' => 'required',
									], $this->backup),
									new Form\LinkedLabel('Interval', 'interval')
								),
								self::COLUMNS
							),
							new Form\BootstrapInput(
								new Form\SafeInput([
									'type' => 'submit',
									'name' => 'act',
									'class' => 'form-control',
									'value' => 'Login',
								], $this->backup),
								self::COLUMNS
							)
						))->render()
					)
				),
				...$this->layout()
			)
		))->render();
	}

	public function actionSubscribe(array $subscription) {
		try {
			$this->protect();
			$url = new Uri\NormalizedUrl(
				new Uri\ReachableUrl(
					new Uri\SchemeForcedUrl(
						new Uri\ValidUrl($subscription['url']),
						['http', 'https']
					)
				)
			);
			(new Storage\PostgresTransaction($this->database))->start(
				function() use ($url, $subscription) {
					$page = (new Subscribing\WebPages($this->database))->add(
						$url,
						new Subscribing\LoggedPage(
							new Subscribing\FrugalPage(
								$url,
								new Subscribing\HtmlWebPage(
									new Http\BasicRequest('GET', $url)
								),
								$this->database
							),
							$this->logger
						)
					);
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts($this->database),
						$this->logger
					))->add(
						new Subscribing\CachedPart(
							new Subscribing\HtmlPart(
								new Subscribing\MatchingExpression(
									new Subscribing\XPathExpression(
										$page,
										$subscription['expression']
									)
								),
								$page
							),
							new Storages\MemoryStorage()
						),
						$url,
						$subscription['expression']
					);
					(new Subscribing\LoggedSubscriptions(
						new Subscribing\LimitedSubscriptions(
							new Subscribing\OwnedSubscriptions(
								$this->subscriber,
								$this->database
							),
							$this->subscriber,
							$this->database
						),
						$this->logger
					))->subscribe(
						$url,
						$subscription['expression'],
						new Time\FutureInterval(
							new Time\LimitedInterval(
								new Time\TimeInterval(
									new \DateTimeImmutable(),
									new \DateInterval(
										sprintf(
											'PT%dM',
											$subscription['interval']
										)
									)
								),
								[
									new Time\TimeInterval(
										new \DateTimeImmutable(),
										new \DateInterval('PT30M')
									),
									new Time\TimeInterval(
										new \DateTimeImmutable(),
										new \DateInterval('PT9000M')
									),
								]
							)
						)
					);
				}
			);
			$this->flashMessage('Subscription has been added', 'success');
			$this->redirect('parts');
		} catch(\Throwable $ex) {
			$this->backup['url'] = $subscription['url'];
			$this->backup['expression'] = $subscription['expression'];
			$this->backup['interval'] = $subscription['interval'];
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('subscription');
		}
	}
}
