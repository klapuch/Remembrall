<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;

final class NewForm extends BootstrapForm {
	private const ACTION = '/subscription', NAME = 'new';
	private $url;
	private $csrf;

	public function __construct(
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		parent::__construct($storage);
		$this->url = $url;
		$this->csrf = $csrf;
	}

	protected function form(): Form\Control {
		return new Form\RawForm(
			self::ATTRIBUTES + [
				'name' => self::NAME,
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						self::URL_ATTRIBUTES + [
							'value' => $_GET['url'] ?? '',
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
							'value' => $_GET['expression'] ?? '',
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
						self::INTERVAL_ATTRIBUTES,
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
						'value' => 'Subscribe',
					],
					$this->storage,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}