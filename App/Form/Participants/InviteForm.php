<?php
declare(strict_types = 1);
namespace Remembrall\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Constraint;
use Remembrall\Model\Subscribing;

final class InviteForm implements Form\Control {
	private const COLUMNS = 3;
	private const ACTION = '/participants/invite',
		NAME = 'invite';
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
		$xml->loadXML($this->subscription->print(new Output\Xml([], 'root'))->serialization());
		return $this->form($xml)->render();
	}

	public function validate(): void {
		$this->form(new \DOMDocument())->validate();
	}

	private function form(\DOMDocument $dom): Form\Control {
		$id = (string) new Form\XmlDynamicValue('id', $dom);
		return new Form\RawForm(
			[
				'id' => sprintf('%s-%s', self::NAME, $id),
				'role' => 'form',
				'class' => 'form-horizontal',
				'name' => sprintf('%s-%s', self::NAME, $id),
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\Input(
				new Form\StoredAttributes(
					['type' => 'hidden', 'name' => 'subscription', 'value' => $id],
					$this->storage
				),
				new Validation\PassiveRule()
			),
			new Form\BootstrapInput(
				new Form\BoundControl(
					new Form\Input(
						new Form\StoredAttributes(
							[
								'type' => 'email',
								'name' => 'email',
								'class' => 'form-control',
								'required' => 'required',
							],
							$this->storage
						),
						new Constraint\EmailRule()
					),
					new Form\LinkedLabel('Email', 'email')
				),
				self::COLUMNS
			),
			new Form\BootstrapInput(
				new Form\Input(
					new Form\FakeAttributes(
						[
							'type' => 'submit',
							'name' => 'act',
							'class' => 'form-control',
							'value' => 'Invite',
						]
					),
					new Validation\PassiveRule()
				),
				self::COLUMNS
			)
		);
	}
}