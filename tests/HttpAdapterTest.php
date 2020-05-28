<?php

declare(strict_types=1);

namespace JustSteveKing\Tests\Graph\Connection\Adapters\Neo4j\Http;

use RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;
use JustSteveKing\Graph\Connection\Adapters\Neo4j\Http\Pipeline;
use JustSteveKing\Graph\Connection\Adapters\Neo4j\Http\HttpAdapter;

class HttpAdapterTest extends TestCase
{
    protected ?string $database = 'phpunit';

    protected string $connectionString = 'http://php:unit@localhost:7474';

    public function buildInstance(): HttpAdapter
    {
        return HttpAdapter::build(
            $this->connectionString,
            $this->database
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_new_instance_of_http_adapter()
    {
        $this->assertInstanceOf(
            HttpAdapter::class,
            $this->buildInstance()
        );
    }

    /**
     * @test
     */
    public function it_throws_a_runtime_exception_on_unsupported_url_schemes()
    {
        $this->expectException(\RuntimeException::class);

        HttpAdapter::build('fail://test:example@notmyhost:1234', 'database');
    }

    /**
     * @test
     */
    public function it_sets_the_database_when_built()
    {
        $adapter = $this->buildInstance();

        $this->assertEquals(
            $this->database,
            $adapter->getDatabase()
        );
    }

    /**
     * @test
     */
    public function it_sets_the_pipeline_when_built()
    {
        $adapter = $this->buildInstance();

        $this->assertInstanceof(
            Pipeline::class,
            $adapter->getPipeline()
        );
    }

    /**
     * @test
     */
    public function it_sets_the_client_when_built()
    {
        $adapter = $this->buildInstance();

        $this->assertInstanceof(
            Client::class,
            $adapter->getClient()
        );
    }

    /**
     * @test
     */
    public function it_returns_a_name_to_be_used_as_an_alias()
    {
        $this->assertEquals(
            'neo-http',
            HttpAdapter::getName()
        );
    }

    /**
     * @test
     */
    public function it_can_set_the_database_using_the_on_method()
    {
        $adapter = $this->buildInstance();

        $this->assertEquals(
            $this->database,
            $adapter->getDatabase()
        );

        $adapter->on('new-database');

        $this->assertEquals(
            'new-database',
            $adapter->getDatabase()
        );
    }

    /**
     * @test
     */
    public function it_can_push_a_new_query_onto_the_pipeline()
    {
        $adapter = $this->buildInstance();

        $this->assertEquals(
            [],
            $adapter->getPipeline()->queries()
        );

        $adapter->query('test query');

        $this->assertEquals(
            ['test query'],
            $adapter->getPipeline()->queries()
        );

        $adapter->query('another test query');

        $this->assertEquals(
            ['test query', 'another test query'],
            $adapter->getPipeline()->queries()
        );
    }

    /**
     * @test
     */
    public function it_can_prepare_the_query_statements()
    {
        $adapter = $this->buildInstance();

        $query = [
            'statement' => 'test statement',
            'resultDataContents' => ['REST', 'GRAPH'],
            'includeStats' => true,
        ];

        $adapter->query('test statement');

        $this->assertEquals(
            json_encode(['statements' => [$query]]),
            $adapter->prepareStatements()
        );

        $anotherQuery = [
            'statement' => 'another statement',
            'resultDataContents' => ['REST', 'GRAPH'],
            'includeStats' => true,
        ];

        $adapter->query('another statement');

        $this->assertEquals(
            json_encode(['statements' => [$query, $anotherQuery]]),
            $adapter->prepareStatements()
        );
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_no_database_has_been_set()
    {
        $this->database = null;

        $adapter = $this->buildInstance();

        $this->expectException(\RuntimeException::class);

        $adapter->send();
    }

    /**
     * @test
     */
    public function it_lets_me_set_a_new_client()
    {
        $adapter = $this->buildInstance();

        $this->assertInstanceof(
            Client::class,
            $adapter->getClient()
        );

        $adapter->setClient(new Client());

        $this->assertInstanceof(
            Client::class,
            $adapter->getClient()
        );
    }

    /**
     * @test
     */
    public function it_returns_a_response_with_data_when_requested()
    {
        $adapter = $this->buildInstance();

        $mockHandler = new MockHandler();

        $httpClient = new Client([
            'handler' => $mockHandler,
        ]);

        $adapter->setClient($httpClient);

        $mockHandler->append(new Response(200, [], json_encode(['test'])));

        $response = $adapter->send();

        $this->assertCount(1, $response);
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_database_server_fails()
    {
        $adapter = $this->buildInstance();

        $mockHandler = new MockHandler();

        $httpClient = new Client([
            'handler' => $mockHandler,
        ]);

        $adapter->setClient($httpClient);

        $mockHandler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));

        $this->expectException(RuntimeException::class);

        $response = $adapter->send();
    }
}
