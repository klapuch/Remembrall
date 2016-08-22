<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Remembrall\Model\Subscribing;
use Klapuch\Output;
use Tracy\Debugger;

final class PartsPage extends BasePage {
    public function renderDefault() {
        $subscriptions = new Subscribing\OwnedSubscriptions(
            $this->subscriber,
            $this->database
        );
        $xmlData = array_reduce(
            $subscriptions->iterate(),
            function($subscriptions, Subscribing\Subscription $subscription) {
                $subscriptions .= $subscription->print(
                    new Output\Xml([], 'subscription')
                );
                return $subscriptions;
            }
        );
        $template = new \DOMDocument();
        $template->load(self::TEMPLATES . '/Parts/default.xml');
        echo (new Output\XsltTemplate(
            self::TEMPLATES . '/Parts/default.xsl',
            new Output\MergedXml(
                $template,
                new \SimpleXMLElement(
                    sprintf(
                        '<%1$s>%2$s</%1$s>',
                        'subscriptions',
                        $xmlData
                    )

                )
            )
        ))->render();
    }
}
