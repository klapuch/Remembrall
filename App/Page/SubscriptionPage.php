<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
    Output, Storage, Uri, Time, Http
};
use Remembrall\Model\Subscribing;
use Nette\Caching\Storages;

final class SubscriptionPage extends BasePage {
    public function renderDefault() {
        echo (new Output\XsltTemplate(
            self::TEMPLATES . '/Subscription/default.xsl',
            new Output\RemoteXml(
                self::TEMPLATES . '/Subscription/default.xml'
            )
        ))->render(['baseUrl' => $this->baseUrl->reference()]);
    }

    public function actionSubscribe() {
        try {
        	$url = new Uri\NormalizedUrl(
        		new Uri\ProtocolBasedUrl(
        			new Uri\ReachableUrl(
        				new Uri\ValidUrl($_POST['url'])
					),
					['http', 'https', '']
				)
			);
			$page = (new Subscribing\WebPages($this->database))->add(
				$url,
				new Subscribing\LoggedPage(
					new Subscribing\CachedPage(
						$url,
						new Subscribing\PostgresPage(
							new Subscribing\HtmlWebPage(
								new Http\BasicRequest('GET', $url)
							),
							$url,
							$this->database
						),
						$this->database
					),
					$this->logger
				)
			);
            (new Storage\PostgresTransaction($this->database))->start(
                function() use ($page, $url) {
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
                        $url,
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
                        $url,
                        $_POST['expression'],
                        new Time\FutureInterval(
                            new Time\LimitedInterval(
                                new Time\TimeInterval(
                                    new \DateTimeImmutable(),
                                    new \DateInterval(
                                        sprintf('PT%dM', $_POST['interval'])
                                    )
                                ),
                                [
                                    new Time\TimeInterval(
                                        new \DateTimeImmutable(),
                                        new \DateInterval('PT30M')
                                    ),
                                    new Time\TimeInterval(
                                        new \DateTimeImmutable(),
                                        new \DateInterval('PT9000M')
                                    )

                                ]
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
