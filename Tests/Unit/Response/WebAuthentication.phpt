<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Unit\Response;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class WebAuthentication extends Tester\TestCase {
	public function testAllowingAccess() {
		Assert::same(
			['foo' => 'bar'],
			(new Response\WebAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser('1', ['role' => 'guest']),
				new Uri\FakeUri(null, 'sign/in')
			))->headers()
		);
	}

	public function testProvidingDefaultRole() {
		Assert::same(
			['foo' => 'bar'],
			(new Response\WebAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser('1', []),
				new Uri\FakeUri(null, 'sign/in')
			))->headers()
		);
	}

	public function testRedirectingOnDeniedAccess() {
		$_SESSION = [];
		Assert::same(
			['Location' => 'localhost/sign/in', 'foo' => 'bar'],
			(new Response\WebAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser('1', ['role' => 'guest']),
				new Uri\FakeUri('localhost', 'sign/out')
			))->headers()
		);
	}

	public function testForbiddenStatusCodeOnDeniedAccess() {
		$_SESSION = [];
		(new Response\WebAuthentication(
			new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
			new Access\FakeUser('1', ['role' => 'guest']),
			new Uri\FakeUri('localhost', 'sign/out')
		))->headers();
		Assert::same(403, http_response_code());
	}

	public function testFlashedMessageOnDeniedAccess() {
		$_SESSION = [];
		(new Response\WebAuthentication(
			new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
			new Access\FakeUser('1', ['role' => 'guest']),
			new Uri\FakeUri('localhost', 'sign/out')
		))->headers();
		Assert::same(
			'dangerYou are not allowed to see the page.',
			(new UI\PersistentFlashMessage($_SESSION))->print(new Output\ArrayFormat([]))->serialization()
		);
	}

	public function testHomepageOnDeniedAccessOnSignInPage() {
		$_SESSION = [];
		Assert::same(
			['Location' => 'localhost/'],
			(new Response\WebAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), []),
				new Access\FakeUser('1', ['role' => 'member']),
				new Uri\FakeUri('localhost', 'sign/in')
			))->headers()
		);
	}

	public function testRewritingStatedLocationHeaderOnDeniedAccess() {
		$_SESSION = [];
		Assert::same(
			['Location' => 'localhost/sign/in', 'foo' => 'bar'],
			(new Response\WebAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar', 'Location' => 'foo']),
				new Access\FakeUser('1', ['role' => 'guest']),
				new Uri\FakeUri('localhost', 'sign/out')
			))->headers()
		);
	}
}

(new WebAuthentication())->run();