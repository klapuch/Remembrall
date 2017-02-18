<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\Form;
use Klapuch\Validation;

final class SignInForm extends Control {
	private const COLUMNS = 4;
	private const ACTION = 'sign/in';

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
							'type' => 'email',
							'name' => 'email',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
						new Validation\ChainedRule(
							new Validation\FriendlyRule(
								new Validation\EmailRule(),
								'Email must be valid'
							),
							new Validation\FriendlyRule(
								new Validation\NegateRule(
									new Validation\EmptyRule()
								),
								'Email must be filled'
							)
						)
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
						new Validation\FriendlyRule(
							new Validation\NegateRule(
								new Validation\EmptyRule()
							),
							'Password must be filled'
						)
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