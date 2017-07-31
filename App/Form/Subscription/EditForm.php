<?php
declare(strict_types = 1);
namespace Remembrall\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;
use Remembrall\Model\Subscribing;

final class EditForm implements Form\Control {
	private const LANGUAGES = ['xpath', 'css'];
	private const COLUMNS = 5;
	private const NAME = 'edit';
	private $subscription;
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		Subscribing\Subscription $subscription,
		Uri\Uri $url,
		Csrf\Protection $csrf,
		Form\Storage $storage
	) {
		$this->subscription = $subscription;
		$this->url = $url;
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	public function render(): string {
		$xml = new \DOMDocument();
		$xml->loadXML($this->subscription->print(new Output\Xml([], self::NAME))->serialization());
		return $this->form($xml)->render();
	}

	public function validate(): void {
		$this->form(new \DOMDocument())->validate();
	}

	private function form(\DOMDocument $dom): Form\Control {
		$language = (string) new Form\XmlDynamicValue('language', $dom);
		$id = (string) new Form\XmlDynamicValue('id', $dom);
		return new Form\RawForm(
			[
				'method' => 'POST',
				'role' => 'form',
				'class' => 'form-horizontal',
				'name' => self::NAME,
				'action' => $this->url->reference() . '/' . $this->url->path(),
			],
			new Form\CsrfInput($this->csrf),
			new Form\Input(
				new Form\StoredAttributes(
					['type' => 'hidden', 'name' => 'id', 'value' => $id],
					$this->storage
				),
				new Validation\PassiveRule()
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'text',
								'name' => 'url',
								'class' => 'form-control',
								'disabled' => 'true',
								'value' => new Form\XmlDynamicValue('url', $dom),
							],
							new Form\EmptyStorage()
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
								'disabled' => 'true',
								'value' => new Form\XmlDynamicValue('expression', $dom),
							],
							new Form\EmptyStorage()
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
								'disabled' => 'true',
							],
							new Form\EmptyStorage()
						),
						new Form\Option(
							new Form\DependentAttributes(
								[
									'value' => 'xpath',
									'disabled' => 'true',
								],
								new Form\FakeStorage(['language' => $language]),
								'language'
							),
							'XPath',
							new Validation\OneOfRule(self::LANGUAGES)
						),
						new Form\Option(
							new Form\DependentAttributes(
								[
									'value' => 'css',
									'disabled' => 'true',
								],
								new Form\FakeStorage(['language' => $language]),
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
								'value' => new Form\XmlDynamicValue('interval', $dom),
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
							'value' => 'Edit',
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