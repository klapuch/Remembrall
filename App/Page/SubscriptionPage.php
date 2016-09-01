<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use GuzzleHttp;
use Klapuch\{
    Output, Storage, Uri
};
use Remembrall\Model\Subscribing;
use Nette\Caching\Storages;

final class SubscriptionPage extends BasePage {
    public function renderDefault() {
        $template = new \DOMDocument();
        $template->load(self::TEMPLATES . '/Subscription/default.xml');
        echo (new Output\XsltTemplate(
            self::TEMPLATES . '/Subscription/default.xsl',
            new Output\MergedXml(
                $template,
                new \SimpleXMLElement('<empty/>')
            )
        ))->render();
    }

    public function actionSubscribe() {
        try {
        	$url = new Uri\NormalizedUrl(
        		new Uri\ProtocolBasedUrl(
        			new Uri\ReachableUri(
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
								$url,
								new GuzzleHttp\Client(['http_errors' => false])
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
