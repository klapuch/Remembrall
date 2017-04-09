<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Klapuch\Output;
use Remembrall\Model\Web;
use Remembrall\Page\Layout;
use Texy;

final class UnreliablePage extends Layout {
	public function render(array $parameters): Output\Format {
		return (new Page(
			new Web\FormattedParts(
				new Web\UnreliableParts(
					new Web\CollectiveParts($this->database),
					$this->database
				),
				new Texy\Texy(),
				new Dindent\Indenter()
			)
		))->render();
	}
}