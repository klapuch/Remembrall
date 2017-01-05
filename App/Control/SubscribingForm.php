<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\{
	Form, Validation
};

final class SubscribingForm extends Control {
	private const COLUMNS = 5;
	private const ACTION = 'subscription/subscribe';

	protected function create(): Form\Control {
		return new Form\RawForm(
			[
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
				'role' => 'form',
				'class' => 'form-horizontal',
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\SafeInput(
						[
							'type' => 'text',
							'name' => 'url',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
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
					new Form\SafeInput(
						[
							'type' => 'text',
							'name' => 'expression',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
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
					new Form\SafeInput(
						[
							'type' => 'number',
							'name' => 'interval',
							'class' => 'form-control',
							'min' => '30',
							'max' => '1440',
							'required' => 'required',
						],
						$this->storage,
						new Validation\ChainedRule(
							new Validation\FriendlyRule(
								new Validation\NegateRule(
									new Validation\EmptyRule()
								),
								'Interval must be filled'
							),
							new Validation\FriendlyRule(
								new Validation\RangeRule(30, 1440),
								'Interval must be greater than 30'
							)
						)
					),
					new Form\LinkedLabel('Interval', 'interval')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\SafeInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Subscribe',
					],
					$this->storage,
					new Validation\FakeRule()
				),
				self::COLUMNS
			)
		);
	}
}