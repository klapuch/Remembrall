<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\{
	Form, Validation
};

final class SubscribingForm extends Control {
	private const COLUMNS = 5;

	protected function create(): Form\Control {
		return new Form\RawForm(
			['method' => 'POST', 'action' => 'subscribe', 'role' => 'form', 'class' => 'form-horizontal'],
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
						new Validation\FakeRule()
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
						new Validation\FakeRule()
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
							'required' => 'required',
						],
						$this->storage,

						new Validation\FakeRule()
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