<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class FormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testCreatingFullForm() {
		$this->assertMatchesXmlSnapshot(
			(string) new Form(
				'<form role="form" action="test.php">
					<input type="text" name="test"/>
					<button type="submit" name="submit"/>
				</form>'
			)
		);
	}

	public function testApplyingRulesToButton() {
		$this->assertMatchesXmlSnapshot(
			(string) new ButtonForm(
				'<form role="form" action="test.php">
					<input type="text" name="test"/>
					<button type="submit" name="submit1"/>
					<input type="button" name="submit2"/>
					<input type="submit" name="submit3"/>
					<button name="submit4"/>
					<input type="submit" title="Old Title" name="submit5"/>
				</form>',
				'My Title',
				'My Class',
				'Some Value'
			)
		);
	}
}
// @codingStandardsIgnoreStart
final class Form {
	private $input;

	public function __construct(string $input) {
		$this->input = $input;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/form.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($this->input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}

final class ButtonForm {
	private $input;
	private $title;
	private $class;
	private $value;

	public function __construct(string $input, string $title, string $class, string $value) {
		$this->input = $input;
		$this->title = $title;
		$this->class = $class;
		$this->value = $value;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/button_form.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->setParameter('', 'title', $this->title);
		$xslt->setParameter('', 'class', $this->class);
		$xslt->setParameter('', 'value', $this->value);
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($this->input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}
// @codingStandardsIgnoreEnd