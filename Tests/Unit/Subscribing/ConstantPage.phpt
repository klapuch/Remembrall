<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConstantPage extends Tester\TestCase {
    public function testContentTransferedToDom() {
        $content = 'Some Content Here';
        Assert::contains(
            $content,
            (new Subscribing\ConstantPage(
                new Subscribing\FakePage(),
                $content
            ))->content()->saveHTML()
        );
    }
}

(new ConstantPage())->run();
