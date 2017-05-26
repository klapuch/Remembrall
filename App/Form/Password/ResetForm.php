<?php
declare(strict_types = 1);
namespace Remembrall\Form\Password;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;

final class ResetForm implements Form\Control {
	private const COLUMNS = 5,
		NAME = 'reset';
	private $reminder;
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		string $reminder,
		Uri\Uri $url,
		Csrf\Protection $csrf,
		Form\Storage $storage
	) {
		$this->reminder = $reminder;
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
				'action' => $this->url->reference() . '/' . $this->url->path(),
				'name' => self::NAME,
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						new Form\StoredAttributes(
							[
								'type' => 'password',
								'name' => 'password',
								'class' => 'form-control',
								'required' => 'required',
							],
							$this->storage
						),
						new Constraint\PasswordRule()
					),
					new Form\LinkedLabel('New password', 'password')
				),
				self::COLUMNS
			),
			new Form\DefaultInput(
				new Form\StoredAttributes(
					[
						'type' => 'hidden',
						'name' => 'reminder',
						'value' => $this->reminder,
					],
					$this->storage
				),
				new Validation\FriendlyRule(
					new Validation\NegateRule(new Validation\EmptyRule()),
					'Reminder must be filled'
				)
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					new Form\StoredAttributes(
						[
							'type' => 'submit',
							'name' => 'act',
							'class' => 'form-control',
							'value' => 'Change',
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