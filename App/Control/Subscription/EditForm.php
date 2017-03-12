<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Model\Subscribing;

final class EditForm extends BootstrapForm {
	private const NAME = 'edit';
	private $url;
	private $csrf;
	private $subscription;

	public function __construct(
		Subscribing\Subscription $subscription,
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Backup $backup
	) {
		$this->subscription = $subscription;
		$this->url = $url;
		$this->csrf = $csrf;
		parent::__construct($backup);
	}

	protected function form(): Form\Control {
		$xml = new \DOMDocument();
		$xml->loadXML($this->subscription->print(new Output\Xml([], self::NAME))->serialization());
		return new Form\RawForm(
			self::ATTRIBUTES + [
				'name' => self::NAME,
				'action' => $this->url->reference() . '/' . $this->url->path(),
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\XmlReloadedInput(
						self::URL_ATTRIBUTES + [
							'disabled' => 'true',
						],
						$xml,
						$this->backup,
						$this->urlRule()
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\XmlReloadedInput(
						self::EXPRESSION_ATTRIBUTES + [
							'disabled' => 'true',
						],
						$xml,
						$this->backup,
						$this->expressionRule()
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\XmlReloadedInput(
						self::INTERVAL_ATTRIBUTES,
						$xml,
						$this->backup,
						$this->intervalRule()
					),
					new Form\LinkedLabel('Interval', 'interval')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					self::SUBMIT_ATTRIBUTES + [
						'value' => 'Edit',
					],
					$this->backup,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}