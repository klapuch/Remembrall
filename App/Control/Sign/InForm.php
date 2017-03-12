<?php
declare(strict_types = 1);
namespace Remembrall\Control\Sign;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Control;

final class InForm extends Control\HarnessedForm {
	private const COLUMNS = 4;
	private const ACTION = '/sign/in', NAME = 'in';
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
							'type' => 'email',
							'name' => 'email',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->backup,
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
					new Form\DefaultInput(
						[
							'type' => 'password',
							'name' => 'password',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->backup,
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
				new Form\DefaultInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Login',
					],
					$this->backup,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}