<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Klapuch\Output;

final class TestTemplate implements Output\Template {
	private $template;

	public function __construct(Output\Template $template) {
		$this->template = $template;
	}

	public function render(array $variables = []): string {
		return $this->template->render(['nonce' => '', 'base_url' => '']);
	}
}