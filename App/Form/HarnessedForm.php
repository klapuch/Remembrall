<?php
declare(strict_types = 1);
namespace Remembrall\Form;

use Klapuch\Form;

final class HarnessedForm implements Form\Control {
	private $storage;
	private $origin;
	private $onSuccess;

	public function __construct(
		Form\Control $origin,
		Form\Storage $storage,
		callable $onSuccess
	) {
		$this->storage = $storage;
		$this->origin = $origin;
		$this->onSuccess = $onSuccess;
	}

	public function render(): string {
		return $this->origin->render();
	}

	public function validate(): void {
		$this->origin->validate();
		($this->onSuccess)();
		$this->storage->drop();
	}
}