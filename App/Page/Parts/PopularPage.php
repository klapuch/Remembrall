<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Remembrall\Model\Web;
use Texy;

final class PopularPage extends Page {
	protected function parts(): Web\Parts {
		return new Web\FormattedParts(
			new Web\PopularParts(
				new Web\CollectiveParts($this->database),
				$this->database
			),
			new Texy\Texy(),
			new Dindent\Indenter()
		);
	}
}