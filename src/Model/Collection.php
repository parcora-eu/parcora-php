<?php

declare(strict_types=1);

namespace Parcora\Model;

use ArrayIterator;
use Closure;
use Countable;
use Generator;
use IteratorAggregate;
use Traversable;

/**
 * One page of a cursor-paginated list, plus {@see autoPaging()} to walk every
 * page lazily.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
final class Collection implements Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $data
     * @param  null|Closure(T): Collection<T>  $pager  fetches the page after a given last item
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $hasMore,
        private readonly ?Closure $pager = null,
    ) {}

    /** @return T|null */
    public function first(): mixed
    {
        return $this->data[0] ?? null;
    }

    /** @return Traversable<int, T> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    /**
     * Walk across every page, fetching the next as needed.
     *
     * @return Generator<int, T>
     */
    public function autoPaging(): Generator
    {
        $page = $this;

        while (true) {
            foreach ($page->data as $item) {
                yield $item;
            }

            if (! $page->hasMore || $page->pager === null || $page->data === []) {
                return;
            }

            $page = ($page->pager)($page->data[array_key_last($page->data)]);
        }
    }
}
