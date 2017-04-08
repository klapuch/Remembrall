<?php
declare(strict_types = 1);
namespace Remembrall\Control\Sign;

use Klapuch\Validation;
use Remembrall\Control;

abstract class BootstrapForm extends Control\HarnessedForm {
	protected const COLUMNS = 5;
	protected const ATTRIBUTES = [
		'method' => 'POST',
		'role' => 'form',
		'class' => 'form-horizontal',
	];
	protected const EMAIL_ATTRIBUTES = [
		'type' => 'email',
		'name' => 'email',
		'class' => 'form-control',
		'required' => 'required',
	];
	protected const PASSWORD_ATTRIBUTES = [
		'type' => 'password',
		'name' => 'password',
		'class' => 'form-control',
		'required' => 'required',
	];
	protected const SUBMIT_ATTRIBUTES = [
		'type' => 'submit',
		'name' => 'act',
		'class' => 'form-control',
	];

	final protected function emailRule(): Validation\Rule {
		return new Validation\ChainedRule(
			new Validation\FriendlyRule(
				new Validation\NegateRule(new Validation\EmptyRule()),
				'Email must be filled'
			),
			new Validation\FriendlyRule(
				new Validation\EmailRule(),
				'Email must be valid'
			)
		);
	}

	final protected function passwordRule(): Validation\Rule {
		return new Validation\FriendlyRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			'Password must be filled'
		);
	}
}