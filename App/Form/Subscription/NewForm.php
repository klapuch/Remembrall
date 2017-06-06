<?php
declare(strict_types = 1);
namespace Remembrall\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;

final class NewForm implements Form\Control {
	private const LANGUAGES = ['xpath', 'css'];
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
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'text',
								'name' => 'url',
								'class' => 'form-control',
								'required' => 'required',
								'value' => $_GET['url'] ?? '',
							],
							$this->storage
						),
						new Constraint\UrlRule()
					),
					new Form\LinkedLabel('Url', 'url')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'text',
								'name' => 'expression',
								'class' => 'form-control',
								'required' => 'required',
								'value' => $_GET['expression'] ?? '',
							],
							$this->storage
						),
						new Constraint\ExpressionRule()
					),
					new Form\LinkedLabel('Expression', 'expression')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\Select(
						new Form\StoredAttributes(
							[
								'name' => 'language',
								'class' => 'form-control',
							],
							new Form\EmptyStorage()
						),
						new Form\Option(
							new Form\DependentAttributes(
								['value' => 'xpath'],
								$this->storage,
								'language'
							),
							'XPath',
							new Validation\OneOfRule(self::LANGUAGES)
						),
						new Form\Option(
							new Form\DependentAttributes(
								['value' => 'css'],
								$this->storage,
								'language'
							),
							'CSS',
							new Validation\OneOfRule(self::LANGUAGES)
						)
					),
					new Form\LinkedLabel('Query language', 'language')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'number',
								'name' => 'interval',
								'class' => 'form-control',
								'required' => 'required',
								'min' => Constraint\IntervalRule::MIN,
								'max' => Constraint\IntervalRule::MAX,
							],
							$this->storage
						),
						new Constraint\IntervalRule()
					),
					new Form\LinkedLabel('Interval', 'interval')
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
							'value' => 'Subscribe',
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