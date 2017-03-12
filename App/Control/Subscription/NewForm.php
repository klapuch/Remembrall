<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Control;

final class NewForm extends Control\HarnessedForm {
	private const COLUMNS = 5;
	private const ACTION = '/subscription/default', NAME = 'new';
	private $url;
	private $csrf;

	public function __construct(
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Backup $backup
	) {
		$this->url = $url;
		$this->csrf = $csrf;
		parent::__construct($backup);
	}

	protected function form(): Form\Control {
		return new Form\RawForm(
			[
				'name' => self::NAME,
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
				'role' => 'form',
				'class' => 'form-horizontal',
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'text',
							'name' => 'url',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['url'] ?? '',
						],
						$this->backup,
						new Validation\FriendlyRule(
							new Validation\NegateRule(
								new Validation\EmptyRule()
							),
							'Url must be filled'
						)
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'text',
							'name' => 'expression',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['expression'] ?? '',
						],
						$this->backup,
						new Validation\FriendlyRule(
							new Validation\NegateRule(
								new Validation\EmptyRule()
							),
							'Expression must be filled'
						)
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'number',
							'name' => 'interval',
							'class' => 'form-control',
							'min' => '30',
							'max' => '1439',
							'required' => 'required',
						],
						$this->backup,
						new Validation\ChainedRule(
							new Validation\FriendlyRule(
								new Validation\NegateRule(
									new Validation\EmptyRule()
								),
								'Interval must be filled'
							),
							new Validation\FriendlyRule(
								new Validation\RangeRule(30, 1439),
								'Interval must be greater than 30'
							)
						)
					),
					new Form\LinkedLabel('Interval', 'interval')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Subscribe',
					],
					$this->backup,
					new Validation\FakeRule()
				),
				self::COLUMNS
			)
		);
	}
}