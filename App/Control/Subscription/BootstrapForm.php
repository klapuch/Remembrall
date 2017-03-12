<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Validation;
use Remembrall\Control;

abstract class BootstrapForm extends Control\HarnessedForm {
	protected const COLUMNS = 5;
	protected const ATTRIBUTES = [
		'method' => 'POST',
		'role' => 'form',
		'class' => 'form-horizontal',
	];
	protected const URL_ATTRIBUTES = [
		'type' => 'text',
		'name' => 'url',
		'class' => 'form-control',
		'required' => 'required',
	];
	protected const EXPRESSION_ATTRIBUTES = [
		'type' => 'text',
		'name' => 'expression',
		'class' => 'form-control',
		'required' => 'required',
	];
	protected const INTERVAL_ATTRIBUTES = [
		'type' => 'number',
		'name' => 'interval',
		'class' => 'form-control',
		'required' => 'required',
		'min' => '30',
		'max' => '1439',
	];
	protected const SUBMIT_ATTRIBUTES = [
		'type' => 'submit',
		'name' => 'act',
		'class' => 'form-control',
	];

	final protected function urlRule(): Validation\Rule {
		return new Validation\FriendlyRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			'Url must be filled'
		);
	}

	final protected function expressionRule(): Validation\Rule {
		return new Validation\FriendlyRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			'Expression must be filled'
		);
	}

	final protected function intervalRule(): Validation\Rule {
		return new Validation\ChainedRule(
			new Validation\FriendlyRule(
				new Validation\NegateRule(new Validation\EmptyRule()),
				'Interval must be filled'
			),
			new Validation\FriendlyRule(
				new Validation\RangeRule(30, 1439),
				'Interval must be greater than 30'
			)
		);
	}
}