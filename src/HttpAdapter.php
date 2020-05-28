<?php

declare(strict_types=1);

namespace JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters;

use RuntimeException;
use GuzzleHttp\Client;
use JustSteveKing\Graph\Connection\Adapters\AdapterInterface;

class HttpAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected string $database = '';

    /**
     * @var Pipeline
     */
    protected Pipeline $pipeline;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * HttpAdapter constructor.
     * @param string $connectionString
     * @return void
     */
    protected function __construct(string $connectionString, ?string $database)
    {
        $uri = parse_url($connectionString);

        if (! is_null($database)) {
            $this->database = $database;
        }

        $this->pipeline = new Pipeline();

        $this->client = new Client([
            'base_uri' => $uri['scheme'] . '://' . $uri['host'] . ':' . $uri['port'],
            'timeout' => 3,
            'headers' => [
                'Accept' => 'application/json;charset=UTF-8',
                'Authorization' => 'Basic ' . base64_encode($uri['user'] . ':' . $uri['pass']),
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Returns the alias name of the adapter
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'neo-http';
    }

    /**
     * Builds the adapter using a connection string
     *
     * @param string $connectionString
     * @param string|null $database
     * @return static
     */
    public static function build(string $connectionString, ?string $database): self
    {
        $uri = parse_url($connectionString);

        if (! preg_match('/http(s?)/i', $uri['scheme'])) {
            $scheme = $uri['scheme'];
            throw new \RuntimeException("The HttpAdapter only accepts http or https schemes, you sent {$scheme}");
        }

        return new self($connectionString, $database);
    }

    /**
     * Send the request to the neo4j API
     *
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function send()
    {
        if (empty($this->database)) {
            throw new RuntimeException(
                "no database has been selected to perform your queries on, please use the 'on(string \$database\)' method first"
            );
        }
        
        $request = $this->prepareStatements();

        try {
            $response = $this->client->request(
                'POST',
                "/db/$this->database/tx",
                [
                    'json' => json_decode($request)
                ]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $this->database = '';
        $this->pipeline = new Pipeline();

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Set the database you want to query on
     *
     * @param string $database
     *
     * @return self
     */
    public function on(string $database): self
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Push a query onto the transaction pipeline
     *
     * @param string $query
     *
     * @return self
     */
    public function query(string $query): self
    {
        $this->pipeline->push($query);

        return $this;
    }

    /**
     * Prepare statements to be sent
     *
     * @return string
     */
    public function prepareStatements(): string
    {
        $statements = [];

        foreach ($this->pipeline->queries() as $statement) {
            $statement = [
                'statement' => $statement,
                'resultDataContents' => ['REST', 'GRAPH'],
                'includeStats' => true,
            ];

            $statements[] = $statement;
        }

        return json_encode([
            'statements' => $statements
        ]);
    }

    /**
     * A helper method for seeing if a database is selected
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * A helper method to work with the current Pipeline
     *
     * @return Pipeline
     */
    public function getPipeline(): Pipeline
    {
        return $this->pipeline;
    }

    /**
     * A helper method to work with the Guzzle Client for any reason.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * A helper method mainly for testing purposes
     *
     * @return self
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
