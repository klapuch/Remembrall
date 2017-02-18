<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;

abstract class Control implements Form\Control {
	protected $url;
	protected $csrf;
	protected $storage;

	final public function __construct(
		Uri\Uri $url,
		Csrf\Csrf $csrf,
		Form\Storage $storage
	) {
		$this->url = $url;
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