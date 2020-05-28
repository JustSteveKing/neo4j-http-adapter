<?php

declare(strict_types=1);

namespace JustSteveKing\Graph\Connection\Adapters\Neo4j\Http;

class Pipeline
{
    /**
     * @var array
     */
    protected array $queries = [];

    /**
     * @param string $query
     *
     * @return void
     */
    public function push(string $query): void
    {
        $this->queries[] = $query;
    }

    /**
     * @return array
     */
    public function queries(): array
    {
        return $this->queries;
    }
}
