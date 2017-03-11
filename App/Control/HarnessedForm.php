<?php
declare(strict_types = 1);
namespace Remembrall\Control;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;

abstract class HarnessedForm {
	protected $backup;

	public function __construct(Form\Backup $backup) {
		$this->backup = $backup;
	}

	final public function render(): string {
		return $this->create()->render();
	}

	/**
	 * @return mixed
	 */
	final public function submit(callable $onSuccess) {
		$this->create()->validate();
		$result = $onSuccess();
		$this->backup->drop();
		return $result;
	}

	abstract protected function create(): Form\Control;
}