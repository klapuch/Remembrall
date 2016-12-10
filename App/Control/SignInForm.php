<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\{
	Form, Validation
};

final class SignInForm extends Control {
	private const COLUMNS = 4;

	protected function create(): Form\Control {
		return new Form\RawForm(
			['method' => 'POST', 'action' => 'in', 'role' => 'form', 'class' => 'form-horizontal'],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\SafeInput(
						[
							'type' => 'email',
							'name' => 'email',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
						new Validation\FakeRule()
					),
					new Form\LinkedLabel('Email', 'email')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\SafeInput(
						[
							'type' => 'password',
							'name' => 'password',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
						new Validation\FakeRule()
					),
					new Form\LinkedLabel('Password', 'password')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\SafeInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Login',
					],
					$this->storage,
					new Validation\FakeRule()
				),
				self::COLUMNS
			)
		);
	}
}