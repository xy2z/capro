<?php

namespace xy2z\LiteConfigTests;

use PHPUnit\Framework\TestCase;
use xy2z\Capro\Collector;
use xy2z\Capro\PublicView;
use xy2z\Capro\View;

define('CAPRO_SITE_ROOT_DIR', __DIR__ . '/../');
define('CAPRO_PUBLIC_DIR', CAPRO_SITE_ROOT_DIR . 'public');
define('CAPRO_VIEWS_DIR', CAPRO_SITE_ROOT_DIR . 'views');
define('CAPRO_VIEWS_CACHE_DIR', CAPRO_SITE_ROOT_DIR . 'views/cache');
define('CAPRO_STATIC_DIR', CAPRO_SITE_ROOT_DIR . 'static');

class CollectorTest extends TestCase {
	public function setUp(): void {
		// ...
	}

	private static function getArray(): array {
		return [
			new PublicView(new View('first', View::TYPE_PAGE, 'first_label')),
			new PublicView(new View('second', View::TYPE_PAGE)),
			new PublicView(new View('third', View::TYPE_PAGE, 'third_label')),
			new PublicView(new View('fourth', View::TYPE_PAGE)),
			new PublicView(new View('fifth', View::TYPE_COLLECTION)),
			new PublicView(new View('sixth', View::TYPE_TEMPLATE)),
		];
	}

	private static function getArrayWithDuplicates(): array {
		return [
			new PublicView(new View('x', View::TYPE_PAGE)),
			new PublicView(new View('y', View::TYPE_PAGE)),
			new PublicView(new View('z', View::TYPE_PAGE)),
			new PublicView(new View('x', View::TYPE_PAGE)),
		];
	}

	private static function getNotOrderedArray(): array {
		return [
			new PublicView(new View('a', View::TYPE_TEMPLATE)),
			new PublicView(new View('A', View::TYPE_COLLECTION)),
			new PublicView(new View('!', View::TYPE_PAGE)),
			new PublicView(new View('b', View::TYPE_PAGE)),
			new PublicView(new View('C', View::TYPE_TEMPLATE)),
		];
	}

	public function test_count(): void {
		//Compare that "count" method returns count of data used in creating the Collector
		$c = new Collector(self::getArray());

		$this->assertSame(count(self::getArray()), $c->count());
	}

	public function test_first(): void {
		//Compare that "first" method returns first element of data used in creating the Collector
		$c = new Collector(self::getArray());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));
	}

	public function test_last(): void {
		//Compare that "last" method returns last element of data used in creating the Collector
		$c = new Collector(self::getArray());

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));
	}

	public function test_limit(): void {
		$c = new Collector(self::getArray());

		//Pick first two elements
		$c->limit(2, 0);
		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('second', $c->last()->get('path'));

		//Pick last two elements
		$c = new Collector(self::getArray());
		$c->limit(2, 4);
		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('fifth', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));

		//Check that empty data is returned when "limit" overflows
		$c = new Collector(self::getArray());
		$c->limit(0, 10);
		$this->assertSame(0, $c->count());

		//Check that "limit" method call chaining works properly
		$c = new Collector(self::getArray());
		$c->limit(4);
		$c->limit(2, 2);
		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('third', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));
	}

	public function test_reverse(): void {
		$c = new Collector(self::getArray());

		//Check that "reverse" method call works properly
		$c->reverse();
		$this->assertSame(count(self::getArray()), $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('sixth', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('first', $c->last()->get('path'));

		//Check that "reverse" method call chaining works properly
		$c->reverse();
		$this->assertSame(count(self::getArray()), $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));
	}

	public function test_reset(): void {
		$c = new Collector(self::getArray());
		$c->limit(2, 4);

		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('fifth', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));

		$c->reset();
		$this->assertSame(count(self::getArray()), $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));
	}

	public function test_where(): void {
		//Test when "where" gets single match
		$c = new Collector(self::getArray());
		$c->where('path', 'first');

		$this->assertSame(1, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		//Test when "where" gets no data
		$c = new Collector(self::getArray());
		$c->where('path', 'non-existent-data');

		$this->assertSame(0, $c->count());

		//Test when "where" gets multiple matches
		$c = new Collector(self::getArrayWithDuplicates());
		$c->where('path', 'x');

		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('x', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('x', $c->last()->get('path'));
	}

	public function test_where_not(): void {
		//Test when "whereNot" removes one entry
		$c = new Collector(self::getArray());
		$c->whereNot('path', 'first');

		$this->assertSame(5, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));

		//Test when "where" removes nothing
		$c = new Collector(self::getArray());
		$c->whereNot('path', 'non-existent-data');

		$this->assertSame(6, $c->count());

		//Test when "whereNot" removes more than one match
		$c = new Collector(self::getArrayWithDuplicates());
		$c->whereNot('path', 'x');

		$this->assertSame(2, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('y', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('z', $c->last()->get('path'));
	}

	public function test_where_between(): void {
		//Test when "whereBetween" gets single match
		$c = new Collector(self::getArray());
		$c->whereBetween('type', 1, 1);

		$this->assertSame(1, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('fifth', $c->first()->get('path'));

		//Test when "whereBetween" gets no data
		$c = new Collector(self::getArray());
		$c->whereBetween('type', 4, 10);

		$this->assertSame(0, $c->count());

		//Test when "whereBetween" gets multiple match
		$c = new Collector(self::getArray());
		$c->whereBetween('type', 0, 0);

		$this->assertSame(4, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));
	}

	public function test_where_not_between(): void {
		//Test when "whereNotBetween" gets single match
		$c = new Collector(self::getArray());
		$c->whereNotBetween('type', -0.1, 1.9);

		$this->assertSame(1, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('sixth', $c->first()->get('path'));

		//Test when "whereNotBetween" gets no data
		$c = new Collector(self::getArray());
		$c->whereNotBetween('type', 0, 4);

		$this->assertSame(0, $c->count());

		//Test when "whereNotBetween" gets multiple match
		$c = new Collector(self::getArray());
		$c->whereNotBetween('type', 1, 3);

		$this->assertSame(4, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));
	}

	public function test_where_in(): void {
		//Test when "whereIn" gets single match
		$c = new Collector(self::getArray());
		$c->whereIn('path', ['third']);

		$this->assertSame(1, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('third', $c->first()->get('path'));

		//Test when "WhereIn" gets no data
		$c = new Collector(self::getArray());
		$c->whereIn('path', ['non-existent-data1', 'non-existent-data-2']);

		$this->assertSame(0, $c->count());

		//Test when "WhereIn" gets multiple match
		$c = new Collector(self::getArray());
		$c->whereIn('path', ['second', 'third', 'fourth']);

		$this->assertSame(3, $c->count());

		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));

		//Test when "WhereIn" gets single match with duplicate in options
		$c = new Collector(self::getArray());
		$c->whereIn('path', ['first', 'first']);

		$this->assertSame(1, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('first', $c->first()->get('path'));

		//Test when "WhereIn" gets multiple matches with duplicate in options
		$c = new Collector(self::getArray());
		$c->whereIn('path', ['second', 'third', 'third', 'fourth']);

		$this->assertSame(3, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));
	}

	public function test_where_not_in(): void {
		//Test when "whereNotIn" gets single match
		$c = new Collector(self::getArray());
		$c->whereNotIn('path', ['first', 'second', 'fourth', 'fifth', 'sixth']);

		$this->assertSame(1, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('third', $c->first()->get('path'));

		//Test when "WhereNotIn" gets no data
		$c = new Collector(self::getArray());
		$c->whereNotIn('path', ['first', 'second', 'third', 'fourth', 'fifth', 'sixth']);

		$this->assertSame(0, $c->count());

		//Test when "WhereNotIn" gets multiple match
		$c = new Collector(self::getArray());
		$c->whereNotIn('path', ['first', 'third', 'fifth', 'sixth']);

		$this->assertSame(2, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));

		//Test when "WhereNotIn" gets single match with duplicate in options
		$c = new Collector(self::getArray());
		$c->whereNotIn('path', ['first', 'first', 'second', 'fourth', 'fifth', 'sixth']);

		$this->assertSame(1, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('third', $c->first()->get('path'));

		//Test when "WhereNotIn" gets multiple matches with duplicate in options
		$c = new Collector(self::getArray());
		$c->whereNotIn('path', ['first', 'first', 'third', 'fifth', 'sixth']);

		$this->assertSame(2, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));

		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fourth', $c->last()->get('path'));
	}

	public function test_order_by(): void {
		//Test "OrderBy" string case-insensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderBy('path', false);

		$this->assertSame(5, $c->count());
		$expectedSortedPaths = ['!', 'A', 'C', 'a', 'b'];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedPaths[$key], $value->get('path'));
		}

		//Test "OrderBy" string case-sensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderBy('path', true);

		$this->assertSame(5, $c->count());
		$expectedSortedPaths = ['!', 'a', 'A', 'b', 'C'];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedPaths[$key], $value->get('path'));
		}

		//Test "OrderBy" integer case-insensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderBy('type', false);

		$this->assertSame(5, $c->count());
		$expectedSortedTypes = [0, 0, 1, 2, 2];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedTypes[$key], $value->get('type'));
		}

		//Test "OrderBy" integer case-sensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderBy('type', true);

		$this->assertSame(5, $c->count());
		$expectedSortedTypes = [0, 0, 1, 2, 2];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedTypes[$key], $value->get('type'));
		}
	}

	public function test_order_by_desc(): void {
		//Test "OrderByDesc" string case-insensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderByDesc('path', false);

		$this->assertSame(5, $c->count());
		$expectedSortedPaths = ['b', 'a', 'C', 'A', '!'];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedPaths[$key], $value->get('path'));
		}

		//Test "OrderByDesc" string case-sensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderByDesc('path', true);

		$this->assertSame(5, $c->count());
		$expectedSortedPaths = ['C', 'b', 'A', 'a', '!'];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedPaths[$key], $value->get('path'));
		}

		//Test "OrderByDesc" integer case-insensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderByDesc('type', false);

		$this->assertSame(5, $c->count());
		$expectedSortedTypes = [2, 2, 1, 0, 0];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedTypes[$key], $value->get('type'));
		}

		//Test "OrderByDesc" integer case-sensitive
		$c = new Collector(self::getNotOrderedArray());
		$c->orderByDesc('type', true);

		$this->assertSame(5, $c->count());
		$expectedSortedTypes = [2, 2, 1, 0, 0];

		foreach ($c->get() as $key => $value) {
			$this->assertInstanceOf(PublicView::class, $value);
			$this->assertSame($expectedSortedTypes[$key], $value->get('type'));
		}
	}

	public function test_where_has(): void {
		//Test "WhereHas" returns data
		$c = new Collector(self::getArray());
		$c->whereHas('path');
		$this->assertSame(6, $c->count());

		//Test "WhereHas" returns no data
		$c = new Collector(self::getArray());
		$c->whereHas('non-existent-key');
		$this->assertSame(0, $c->count());
	}

	public function test_where_has_not(): void {
		//Test "WhereHasNot" returns data
		$c = new Collector(self::getArray());
		$c->whereHasNot('non-existent-key');
		$this->assertSame(6, $c->count());

		//Test "WhereHasNot" returns no data
		$c = new Collector(self::getArray());
		$c->whereHasNot('path');
		$this->assertSame(0, $c->count());
	}

	public function test_exclude(): void {
		//Test "Exclude" called with object other than PublicView
		$c = new Collector(self::getArray());
		try {
			$c->exclude([1, 2]);
		} catch (\Exception $e) {
			$this->assertSame(\Exception::class, get_class($e));
			$this->assertSame('exclude() expects an array of PublicView objects.', $e->getMessage());
		}

		//Test "Exclude" called with one entry removes single entry
		$c = new Collector(self::getArray());
		$c->exclude($c->first());
		$this->assertSame(5, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));
		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('sixth', $c->last()->get('path'));

		//Test "Exclude" called with one entry removes multiple entries
		$c = new Collector(self::getArrayWithDuplicates());
		$c->exclude($c->first());
		$this->assertSame(2, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('y', $c->first()->get('path'));
		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('z', $c->last()->get('path'));

		//Test "Exclude" called with multiple entries removes multiple entries
		$c = new Collector(self::getArray());
		$c->exclude([
			$c->first(),
			$c->last(),
		]);
		$this->assertSame(4, $c->count());
		$this->assertInstanceOf(PublicView::class, $c->first());
		$this->assertSame('second', $c->first()->get('path'));
		$this->assertInstanceOf(PublicView::class, $c->last());
		$this->assertSame('fifth', $c->last()->get('path'));

		//Test "Exclude" called with multiple entries removes no entries
		$c = new Collector(self::getArray());
		$c->exclude([
			new PublicView(new View('non-existent-path-1', View::TYPE_PAGE, 'first_label')),
			new PublicView(new View('non-existent-path-2', View::TYPE_TEMPLATE)),
		]);
		$this->assertSame(6, $c->count());

		//Test "Exclude" called with multiple entries removes all entries
		$c = new Collector(self::getArray());
		$c->exclude(self::getArray());
		$this->assertSame(0, $c->count());
	}

	public function test_shuffle(): void {
		$c = new Collector(self::getArray());
		$c->shuffle();

		//Due to randomness of shuffle, we cannot test if array was really randomized,
		// we can only test if we still have all elements in array
		$this->assertSame(6, $c->count());
	}
}
