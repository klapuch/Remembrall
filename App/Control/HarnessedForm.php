<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\Form;

abstract class HarnessedForm {
	protected $storage;

	public function __construct(Form\Storage $storage) {
		$this->storage = $storage;
	}

	final public function render(): string {
		return $this->form()->render();
	}

	/**
	 * @return mixed
	 */
	final public function submit(callable $onSuccess) {
		$this->form()->validate();
		$result = $onSuccess();
		$this->storage->drop();
		return $result;
	}

	abstract protected function form(): Form\Control;
}