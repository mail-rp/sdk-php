<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * One page of a search: the rows of the page and the total number of matching items.
 *
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 */
final readonly class Page implements \IteratorAggregate, \Countable
{
    /**
     * @param int     $total number of items matching the search, every page included
     * @param list<T> $rows  items of this page
     */
    public function __construct(
        public int $total,
        public array $rows,
    ) {
    }

    /**
     * @template R
     *
     * @param array<mixed>              $data  decoded `{total, rows}` response
     * @param callable(array<mixed>): R $build builds one row
     *
     * @return self<R>
     *
     * @internal
     */
    public static function fromArray(array $data, callable $build): self
    {
        $rows = [];
        foreach (Data::array($data, 'rows') as $row) {
            $rows[] = $build(is_array($row) ? $row : ['value' => $row]);
        }

        return new self(Data::int($data, 'total'), $rows);
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->rows);
    }

    /**
     * Number of rows in this page (see {@see $total} for the whole search).
     */
    public function count(): int
    {
        return count($this->rows);
    }
}
