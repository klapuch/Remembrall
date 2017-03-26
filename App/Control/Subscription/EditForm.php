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
		Form\Storage $storage
	) {
		parent::__construct($storage);
		$this->subscription = $subscription;
		$this->url = $url;
		$this->csrf = $csrf;
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
					new Form\DefaultInput(
						self::URL_ATTRIBUTES + [
							'disabled' => 'true',
							'value' => new Form\XmlDynamicValue('url', $xml),
						],
						$this->storage,
						$this->urlRule()
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						self::EXPRESSION_ATTRIBUTES + [
							'disabled' => 'true',
							'value' => new Form\XmlDynamicValue('expression', $xml),
						],
						$this->storage,
						$this->expressionRule()
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						self::INTERVAL_ATTRIBUTES + [
							'value' => new Form\XmlDynamicValue('interval', $xml),
						],
						$this->storage,
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
					$this->storage,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}