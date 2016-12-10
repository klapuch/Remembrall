<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\{
	Form, Csrf
};

abstract class Control implements Form\Control {
	protected $csrf;
	protected $storage;

	final public function __construct(Csrf\Csrf $csrf, Form\Storage $storage) {
		$this->csrf = $csrf;
		$this->storage = $storage;
	}

	final public function render(): string {
		return $this->create()->render();
	}

	final public function validate(): void {
		$this->create()->validate();
	}

	abstract protected function create(): Form\Control;
}