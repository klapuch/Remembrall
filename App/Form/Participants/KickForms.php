<?php
declare(strict_types = 1);
namespace Remembrall\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;

final class KickForms implements Form\Control {
	private $participants;
	private $url;
	private $csrf;
	private $storage;

	public function __construct(
		array $participants,
		Uri\Uri $url,
		Csrf\Protection $csrf,
		Form\Storage $storage
	) {
		$this->participants = $participants;
		$this->url = $url;
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	public function validate(): void {
		// It is not needed
	}

	public function render(): string {
		return array_reduce(
			$this->participants,
			function(string $forms, Subscribing\Participant $participant): string {
				return $forms .= (new KickForm(
					$participant,
					$this->url,
					$this->csrf,
					$this->storage
				))->render();
			},
			''
		);
	}
}