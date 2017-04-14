<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

final class ComposedResponse implements Application\Response {
	private $origin;
	private $template;
	private $layout;

	public function __construct(
		Application\Response $origin,
		string $template,
		string $layout
	) {
		$this->origin = $origin;
		$this->template = $template;
		$this->layout = $layout;
	}

	public function body(): Output\Format {
		$layout = new \DOMDocument();
		$layout->load($this->layout);
		$template = new \DOMDocument();
		$template->load($this->template);
		return new Output\MergedXml(
			$template,
			...array_merge(
				simplexml_import_dom($layout)->xpath('child::*'),
				(new \SimpleXMLElement(
					(new Output\WrappedXml(
						'page',
						$this->origin->body()
					))->serialization()
				))->xpath('child::*')
			)
		);
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		] + $this->origin->headers();
	}
}