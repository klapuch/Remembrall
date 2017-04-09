<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Remembrall\Constraint;
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
		'min' => Constraint\IntervalRule::MIN,
		'max' => Constraint\IntervalRule::MAX,
	];
	protected const SUBMIT_ATTRIBUTES = [
		'type' => 'submit',
		'name' => 'act',
		'class' => 'form-control',
	];
}