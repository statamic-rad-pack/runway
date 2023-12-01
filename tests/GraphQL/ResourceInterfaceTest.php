<?php

namespace DoubleThreeDigital\Runway\Tests\GraphQL;

use DoubleThreeDigital\Runway\Tests\TestCase;
use Rebing\GraphQL\Support\Facades\GraphQL;

class ResourceInterfaceTest extends TestCase
{
    /** @test */
    public function it_adds_types()
    {
        $this->assertEquals([
            'runway_graphql_types_post' => 'runway_graphql_types_post',
            'runway_graphql_types_author' => 'runway_graphql_types_author',
        ], GraphQL::getTypes());
    }
}
