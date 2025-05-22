<?php

namespace StatamicRadPack\Runway\Tests\GraphQL;

use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourceInterfaceTest extends TestCase
{
    #[Test]
    public function it_adds_types()
    {
        $this->assertEquals([
            'runway_graphql_types_post',
            'Runway_NestedFields_Post_Values',
            'Runway_NestedFields_Post_ExternalLinks',
            'runway_graphql_types_author',
            'runway_graphql_types_user',
        ], array_keys(GraphQL::getTypes()));
    }
}
