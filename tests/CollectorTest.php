<?php

namespace xy2z\LiteConfigTests;

use PHPUnit\Framework\TestCase;
use xy2z\Capro\Collector;
use xy2z\Capro\PublicView;
use xy2z\Capro\View;

define('SITE_ROOT_DIR', __DIR__ . '/../');
define('PUBLIC_DIR', SITE_ROOT_DIR . 'public');
define('VIEWS_DIR', SITE_ROOT_DIR . 'views');
define('VIEWS_CACHE_DIR', SITE_ROOT_DIR . 'views/cache');
define('STATIC_DIR', SITE_ROOT_DIR . 'static');

class CollectorTest extends TestCase {

	public function setUp(): void {
		// ...
	}

	private static function getArray(): array {
		return [
			new PublicView(new View('about', View::TYPE_PAGE)),
		];
	}

	public function test_count() {
		$c = new Collector(self::getArray());
		$this->assertEquals(1, $c->count());
		$this->assertCount(1, $c->get());
	}

	public function test_first() {
		$c = new Collector(self::getArray());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('about', $c->first()->get('path'));
	}

}
