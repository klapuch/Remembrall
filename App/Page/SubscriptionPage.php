<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use GuzzleHttp;
use Klapuch\{
    Output, Storage
};
use Remembrall\Model\Subscribing;
use Nette\Caching\Storages;

final class SubscriptionPage extends BasePage {
    public function renderDefault() {
        $xml = new \DOMDocument();
        $xml->load(self::TEMPLATES . '/Subscription/default.xml');
        echo (new Output\XsltTemplate(
            self::TEMPLATES . '/Subscription/default.xsl',
            new \SimpleXMLElement($xml->saveXML())
        ))->render();
    }

    public function actionSubscribe() {
        try {
            $page = new Subscribing\LoggedPage(
                new Subscribing\CachedPage(
                    $_POST['url'],
                    new Subscribing\HtmlWebPage(
                        $_POST['url'],
                        new GuzzleHttp\Client(['http_errors' => false])
                    ),
                    new Subscribing\WebPages($this->database),
                    $this->database
                ),
                $this->logger
            );
            (new Storage\PostgresTransaction($this->database))->start(
                function() use ($page) {
                    (new Subscribing\LoggedParts(
                        new Subscribing\CollectiveParts(
                            $this->database
                        ),
                        $this->logger
                    ))->add(
                        new Subscribing\CachedPart(
                            new Subscribing\HtmlPart(
                                new Subscribing\ValidXPathExpression(
                                    new Subscribing\XPathExpression(
                                        $page,
                                        $_POST['expression']
                                    )
                                ),
                                $page
                            ),
                            new Storages\MemoryStorage()
                        ),
                        $_POST['url'],
                        $_POST['expression']
                    );
                    (new Subscribing\LoggedSubscriptions(
                        new Subscribing\LimitedSubscriptions(
                            $this->database,
                            $this->subscriber,
                            new Subscribing\OwnedSubscriptions(
                                $this->subscriber,
                                $this->database
                            )
                        ),
                        $this->logger
                    ))->subscribe(
                        $_POST['url'],
                        $_POST['expression'],
                        new Subscribing\FutureInterval(
                            new Subscribing\DateTimeInterval(
                                new \DateTimeImmutable(),
                                new \DateInterval(
                                    sprintf('PT%dM', max(0, $_POST['interval']))
                                )
                            )
                        )
                    );
                }
            );
            header(
                sprintf(
                    'Location: %s', $this->link('Parts:default')
                )
            );
            exit;
        } catch(\Throwable $ex) {
            echo $ex->getMessage();
        }
    }
}
