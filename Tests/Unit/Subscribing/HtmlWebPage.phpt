<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\Exception\NotFoundException;
use Tester;
use Tester\Assert;
use Klapuch\Http;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends Tester\TestCase {
    public function testContentAsValidDom() {
        Assert::contains(
            'Hi, there!',
            (new Subscribing\HtmlWebPage(
                new Http\FakeRequest(
                    new Http\FakeResponse(
                        'Hi, there!',
                        ['Content-Type' => 'text/html'],
                        200
                    )
                )
            ))->content()->saveHtml()
        );
    }

    public function testErrorWithPassedPreviousMessage() {
        $previous = Assert::exception(function() {
            (new Subscribing\HtmlWebPage(
                new Http\FakeRequest(
                    new Http\FakeResponse(
                        'Hi, there!',
                        ['Content-Type' => 'text/html'],
                        404
                    )
                )
            ))->content();
        }, NotFoundException::class, 'Page is unreachable. Does the URL exist?');
        Assert::type(\Exception::class, $previous);
    }

    public function testRefreshing() {
        $page = new Subscribing\HtmlWebPage(new Http\FakeRequest());
        Assert::equal($page, $page->refresh());
    }
}

(new HtmlWebPage())->run();
