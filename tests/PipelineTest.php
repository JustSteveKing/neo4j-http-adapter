<?php

declare(strict_types=1);

namespace JustSteveKing\Tests\Graph\Connection\Adapters\Neo4j\Adapters\Http;

use PHPUnit\Framework\TestCase;
use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\Http\Pipeline;

class PipelineTest extends TestCase
{
    public function buildInstance(): Pipeline
    {
        return new Pipeline();
    }

    /**
     * @test
     */
    public function it_can_create_a_new_instance_of_pipeline()
    {
        $this->assertInstanceOf(
            Pipeline::class,
            $this->buildInstance()
        );
    }

    /**
     * @test
     */
    public function it_is_created_with_an_empty_array_of_queries()
    {
        $pipeline = $this->buildInstance();

        $this->assertIsArray(
            $pipeline->queries()
        );

        $this->assertTrue(
            empty($pipeline->queries())
        );
    }

    /**
     * @test
     */
    public function it_can_push_new_queries_into_the_queries_array()
    {
        $pipeline = $this->buildInstance();
        $this->assertTrue(
            empty($pipeline->queries())
        );

        $pipeline->push('test');

        $this->assertTrue(
            ! empty($pipeline->queries())
        );

        $this->assertEquals(
            1,
            count($pipeline->queries())
        );
    }
}
