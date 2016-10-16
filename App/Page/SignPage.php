<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Output;
use Remembrall\Model\Subscribing;

final class SignPage extends BasePage {
	public function renderIn() {
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Sign/in.xsl',
			new Output\RemoteXml(self::TEMPLATES . '/Sign/in.xml')
		))->render([
			'baseUrl' => $this->url->reference(),
		]);
	}
}