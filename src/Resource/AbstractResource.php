<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Exception\UnexpectedResponseException;
use Xelyos\MailRp\Http\ApiRequester;
use Xelyos\MailRp\Model\Page;

/**
 * Search parameters shared by every `search()` / `iterate()` method:
 * - `search`: text to look for (the searched fields depend on the resource)
 * - `limit`: 1 to 100 (10 by default on the API side; page size for `iterate()`, 100 by default)
 * - `offset`: number of items to skip
 * - `sort` / `order`: sort field (whitelist per resource) and `asc` / `desc`
 *
 * @phpstan-type SearchQuery array{search?: string|null, limit?: int|null, offset?: int|null, sort?: string|null, order?: \Xelyos\MailRp\Enum\SortOrder|string|null}
 */
abstract class AbstractResource
{
    /** Largest page the API accepts */
    public const MAX_LIMIT = 100;

    public function __construct(
        protected readonly ApiRequester $requester,
    ) {
    }

    /**
     * Builds a path from a pattern and its segments, each one URL-encoded (`/user/%s/alias/%s`).
     */
    protected static function path(string $pattern, string ...$segments): string
    {
        return vsprintf($pattern, array_map(ApiRequester::segment(...), $segments));
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<mixed>
     *
     * @throws \JsonException
     */
    protected function getJson(string $path, array $query = []): array
    {
        return $this->expectBody($this->requester->request('GET', $path, $query), 'GET', $path);
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<mixed>
     *
     * @throws \JsonException
     */
    protected function sendJson(string $method, string $path, ?array $body = null): array
    {
        return $this->expectBody($this->requester->request($method, $path, [], $body), $method, $path);
    }

    /**
     * Request answered by `204 No Content`.
     *
     * @param array<string, mixed> $query
     *
     * @throws \JsonException
     */
    protected function sendEmpty(string $method, string $path, array $query = []): void
    {
        $this->requester->request($method, $path, $query);
    }

    /**
     * Fetches the pages one after the other and yields every row, until the total is reached.
     *
     * @template T
     *
     * @param \Closure(SearchQuery): Page<T> $fetch
     * @param SearchQuery                    $query
     *
     * @return \Generator<int, T>
     */
    protected static function paginate(\Closure $fetch, array $query): \Generator
    {
        $limit = $query['limit'] ?? self::MAX_LIMIT;
        $offset = $query['offset'] ?? 0;

        do {
            $query['limit'] = $limit;
            $query['offset'] = $offset;
            $page = $fetch($query);
            foreach ($page->rows as $row) {
                yield $row;
            }
            $offset += count($page->rows);
        } while ([] !== $page->rows && $offset < $page->total);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function expectBody(?array $data, string $method, string $path): array
    {
        return $data ?? throw new UnexpectedResponseException(sprintf('The Mail RP API returned an empty body for %s %s', $method, $path));
    }
}
