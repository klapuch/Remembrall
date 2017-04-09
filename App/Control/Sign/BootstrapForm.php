<?php
declare(strict_types = 1);
namespace Remembrall\Control\Sign;

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
}