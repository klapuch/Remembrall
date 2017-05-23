<?php
declare(strict_types = 1);
namespace Remembrall\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Model\Subscribing;

final class DeleteForm implements Form\Control {
	private const ACTION = '/subscription/delete', NAME = 'delete';
	private $subscription;
	private $url;
	private $csrf;

	public function __construct(
		Subscribing\Subscription $subscription,
		Uri\Uri $url,
		Csrf\Protection $csrf
	) {
		$this->subscription = $subscription;
		$this->url = $url;
		$this->csrf = $csrf;
	}

	public function render(): string {
		$xml = new \DOMDocument();
		$xml->loadXML($this->subscription->print(new Output\Xml([], 'root'))->serialization());
		return $this->form($xml)->render();
	}

	public function validate(): void {
		// It is not needed
	}

	private function form(\DOMDocument $dom): Form\Control {
		$id = (string) new Form\XmlDynamicValue('id', $dom);
		return new Form\RawForm(
			[
				'id' => self::NAME,
				'name' => sprintf('%s-%s', self::NAME, $id),
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\DefaultInput(
				[
					'type' => 'hidden',
					'name' => 'id',
					'value' => $id,
				],
				new Form\EmptyStorage(),
				new Validation\PassiveRule()
			),
			new Form\DefaultInput(
				['type' => 'submit'],
				new Form\EmptyStorage(),
				new Validation\PassiveRule()
			)
		);
	}
}