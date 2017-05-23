<?php
declare(strict_types = 1);
namespace Remembrall\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Model\Subscribing;

final class RetryForm implements Form\Control {
	private const ACTION = '/participants/invite',
		NAME = 'retry';
	private $participant;
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		Subscribing\Participant $participant,
		Uri\Uri $url,
		Csrf\Protection $csrf,
		Form\Storage $storage
	) {
		$this->participant = $participant;
		$this->url = $url;
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	public function render(): string {
		$xml = new \DOMDocument();
		$xml->loadXML($this->participant->print(new Output\Xml([], 'root'))->serialization());
		return $this->form($xml)->render();
	}

	public function validate(): void {
		// It is not needed
	}

	private function form(\DOMDocument $dom): Form\Control {
		$id = (string) new Form\XmlDynamicValue('id', $dom);
		$subscription = (string) new Form\XmlDynamicValue('subscription_id', $dom);
		$email = (string) new Form\XmlDynamicValue('email', $dom);
		return new Form\RawForm(
			[
				'id' => self::NAME,
				'name' => sprintf('%s-%s', self::NAME, $id),
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\DefaultInput(
				['type' => 'hidden', 'name' => 'subscription', 'value' => $subscription],
				new Form\EmptyStorage(),
				new Validation\PassiveRule()
			),
			new Form\DefaultInput(
				['type' => 'hidden', 'name' => 'email', 'value' => $email],
				new Form\EmptyStorage(),
				new Validation\PassiveRule()
			),
			new Form\DefaultInput(
				['type' => 'submit', 'name' => 'act'],
				$this->storage,
				new Validation\PassiveRule()
			)
		);
	}
}