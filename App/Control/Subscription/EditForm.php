<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Remembrall\Subscribing;
use Remembrall\Control;
use Klapuch\Markup;
use Klapuch\Form;
use Klapuch\Validation;

final class EditForm extends Control\HarnessedForm {
	private const COLUMNS = 5;
	private const ACTION = '/subscription/edit',
		NAME = 'edit';

	protected function create(): Form\Control {
		return new Form\RawForm(
			new Markup\ConcatenatedAttribute(
				new Markup\SafeAttribute('name', self::NAME),
				new Markup\SafeAttribute('method', 'POST'),
				new Markup\SafeAttribute('action', $this->url->reference() . self::ACTION),
				new Markup\SafeAttribute('role', 'form'),
				new Markup\SafeAttribute('class', 'form-horizontal')
			),
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\PersistentInput(
						[
							'type' => 'text',
							'name' => 'url',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['url'] ?? '',
						],
						$this->backup,
						new Validation\FriendlyRule(
							new Validation\NegateRule(
								new Validation\EmptyRule()
							),
							'Url must be filled'
						)
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\PersistentInput(
						[
							'type' => 'text',
							'name' => 'expression',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['expression'] ?? '',
						],
						$this->backup,
						new Validation\FriendlyRule(
							new Validation\NegateRule(
								new Validation\EmptyRule()
							),
							'Expression must be filled'
						)
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\PersistentInput(
						[
							'type' => 'number',
							'name' => 'interval',
							'class' => 'form-control',
							'min' => '30',
							'max' => '1439',
							'required' => 'required',
						],
						$this->backup,
						new Validation\ChainedRule(
							new Validation\FriendlyRule(
								new Validation\NegateRule(
									new Validation\EmptyRule()
								),
								'Interval must be filled'
							),
							new Validation\FriendlyRule(
								new Validation\RangeRule(30, 1439),
								'Interval must be greater than 30'
							)
						)
					),
					new Form\LinkedLabel('Interval', 'interval')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\XslInput([
					new Form\StaticXslAttribute('type', 'submit'),
					new Form\StaticXslAttribute('name', 'act'),
					new Form\StaticXslAttribute('class', 'form-control'),
					new Form\DynamicXslAttribute('value', '/page/head/title')
				]),
				self::COLUMNS
			)
		);
	}
}