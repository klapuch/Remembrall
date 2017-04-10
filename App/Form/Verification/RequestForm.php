<?php
declare(strict_types = 1);
namespace Remembrall\Form\Verification;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;

final class RequestForm implements Form\Control {
	private const COLUMNS = 5;
	private const ACTION = '/verification/request',
		NAME = 'request';
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		$this->url = $url;
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	public function render(): string {
		return $this->form()->render();
	}

	public function validate(): void {
		$this->form()->validate();
	}

	private function form(): Form\Control {
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
						new Constraint\EmailRule()
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