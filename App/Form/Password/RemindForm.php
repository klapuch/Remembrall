<?php
declare(strict_types = 1);
namespace Remembrall\Form\Password;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;

final class RemindForm implements Form\Control {
	private const COLUMNS = 5;
	private const ACTION = '/password/remind',
		NAME = 'remind';
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		Uri\Uri $url,
		Csrf\Protection $csrf,
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
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'email',
								'name' => 'email',
								'class' => 'form-control',
								'required' => 'required',
							],
							$this->storage
						),
						new Constraint\EmailRule()
					),
					new Form\LinkedLabel('Email', 'email')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\Input(
					new Form\StoredAttributes(
						[
							'type' => 'submit',
							'name' => 'act',
							'class' => 'form-control',
							'value' => 'Send',
						],
						$this->storage
					),
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}