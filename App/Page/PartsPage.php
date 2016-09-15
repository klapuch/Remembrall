<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Remembrall\Model\Subscribing;
use Klapuch\{
    Output, Uri
};

final class PartsPage extends BasePage {
	public function renderDefault() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Parts/default.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Parts/default.xsl',
			new Output\MergedXml(
				$xml,
				new \SimpleXMLElement(
					sprintf(
						'<%1$s>%2$s</%1$s>',
						'subscriptions',
						array_reduce(
							(new Subscribing\OwnedSubscriptions(
								$this->subscriber,
								$this->database
							))->iterate(),
							function(
								$subscriptions,
								Subscribing\Subscription $subscription
							) {
								$subscriptions .= $subscription->print(
									new Output\Xml([], 'subscription')
								);
								return $subscriptions;
							}
						)
					)
				)
			)
        ))->render([
            'baseUrl' => $this->baseUrl->reference(),
        ]);
    }

    public function handleDelete($expression, $url) {
        try {
            (new Subscribing\OwnedSubscription(
                new Uri\ValidUrl($url),
                $expression,
                $this->subscriber,
                $this->database
            ))->cancel();
        } catch(\Throwable $ex) {
            echo $ex->getMessage();
        }
    }
}
