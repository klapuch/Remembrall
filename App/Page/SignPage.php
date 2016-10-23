<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Output;
use Remembrall\Model\Subscribing;

final class SignPage extends BasePage {
	public function renderIn() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Sign/in.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Sign/in.xsl',
			new Output\MergedXml($xml, ...$this->layout())
		))->render();
	}
}