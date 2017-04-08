<?php
declare(strict_types = 1);
namespace Remembrall\Control\Verification;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Control;

final class RequestForm extends Control\HarnessedForm {
	private const COLUMNS = 5;
	private const ACTION = '/verification/request', NAME = 'request';
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
			[
				'method' => 'POST',
				'role' => 'form',
				'class' => 'form-horizontal',
				'action' => $this->url->reference() . self::ACTION,
				'name' => self::NAME,
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
						$this->storage,
						new Validation\ChainedRule(
							new Validation\FriendlyRule(
								new Validation\NegateRule(
									new Validation\EmptyRule()
								),
								'Email must be filled'
							),
							new Validation\FriendlyRule(
								new Validation\EmailRule(),
								'Email must be valid'
							)
						)
					),
					new Form\LinkedLabel('Email', 'email')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Send',
					],
					$this->storage,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}