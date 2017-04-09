<?php
declare(strict_types = 1);
namespace Remembrall\Control\Password;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;
use Remembrall\Control;

final class ResetForm extends Control\HarnessedForm {
	private const COLUMNS = 5;
	private const NAME = 'reset';
	private $reminder;
	private $url;
	private $csrf;

	public function __construct(
		string $reminder,
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		parent::__construct($storage);
		$this->reminder = $reminder;
		$this->url = $url;
		$this->csrf = $csrf;
	}

	protected function form(): Form\Control {
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
						[
							'type' => 'password',
							'name' => 'password',
							'class' => 'form-control',
							'required' => 'required',
						],
						$this->storage,
						new Constraint\PasswordRule()
					),
					new Form\LinkedLabel('New password', 'password')
				),
				self::COLUMNS
			),
			new Form\DefaultInput(
				[
					'type' => 'hidden',
					'name' => 'reminder',
					'value' => $this->reminder,
				],
				$this->storage,
				new Validation\FriendlyRule(
					new Validation\NegateRule(new Validation\EmptyRule()),
					'Reminder must be filled'
				)
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Change',
					],
					$this->storage,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}