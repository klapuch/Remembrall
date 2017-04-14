<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;

final class FormResponse implements Application\Response {
	private $forms;

	public function __construct(Form\Control ...$forms) {
		$this->forms = $forms;
	}

	public function body(): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				implode(
					array_map(
						function(Form\Control $form): string {
							return $form->render();
						},
						$this->forms
					)
				)
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		];
	}
}