<?php
declare(strict_types = 1);
namespace Remembrall\Control\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Remembrall\Control;
use Remembrall\Model\Subscribing;

final class DeleteForm extends Control\HarnessedForm {
	private const ACTION = '/subscription/delete', NAME = 'delete';
	private $subscription;
	private $url;
	private $csrf;

	public function __construct(
		Subscribing\Subscription $subscription,
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		parent::__construct($storage);
		$this->subscription = $subscription;
		$this->url = $url;
		$this->csrf = $csrf;
	}

	protected function form(): Form\Control {
		$xml = new \DOMDocument();
		$xml->loadXML($this->subscription->print(new Output\Xml([], 'root'))->serialization());
		$id = (string) new Form\XmlDynamicValue('id', $xml);
		return new Form\RawForm(
			[
				'id' => self::NAME,
				'name' => sprintf('%s-%d', self::NAME, $id),
				'method' => 'POST',
				'action' => $this->url->reference() . self::ACTION,
			],
			new Form\CsrfInput($this->csrf),
			new Form\DefaultInput(
				['type' => 'hidden', 'name' => 'id', 'value' => $id],
				$this->storage,
				new Validation\PassiveRule()
			),
			new Form\DefaultInput(
				['type' => 'submit'],
				$this->storage,
				new Validation\PassiveRule()
			)
		);
	}
}