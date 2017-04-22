<?php
declare(strict_types = 1);
namespace Remembrall\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;

final class NewForm implements Form\Control {
	private const COLUMNS = 5;
	private const ACTION = '/subscription',
		NAME = 'new';
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
				'name' => self::NAME,
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'text',
							'name' => 'url',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['url'] ?? '',
						],
						$this->storage,
						new Constraint\UrlRule()
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'text',
							'name' => 'expression',
							'class' => 'form-control',
							'required' => 'required',
							'value' => $_GET['expression'] ?? '',
						],
						$this->storage,
						new Constraint\ExpressionRule()
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\DefaultInput(
						[
							'type' => 'number',
							'name' => 'interval',
							'class' => 'form-control',
							'required' => 'required',
							'min' => Constraint\IntervalRule::MIN,
							'max' => Constraint\IntervalRule::MAX,
						],
						$this->storage,
						new Constraint\IntervalRule()
					),
					new Form\LinkedLabel('Interval', 'interval')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\DefaultInput(
					[
						'type' => 'submit',
						'name' => 'act',
						'class' => 'form-control',
						'value' => 'Subscribe',
					],
					$this->storage,
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}