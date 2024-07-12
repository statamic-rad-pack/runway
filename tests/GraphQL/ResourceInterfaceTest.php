<?php

namespace StatamicRadPack\Runway\Tests\GraphQL;

use Rebing\GraphQL\Support\Facades\GraphQL;
use StatamicRadPack\Runway\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResourceInterfaceTest extends TestCase
{
    #[Test]
    public function it_adds_types()
    {
        $this->assertEquals([
            'runway_graphql_types_post' => 'runway_graphql_types_post',
            'runway_graphql_types_author' => 'runway_graphql_types_author',
            'runway_graphql_types_user' => 'runway_graphql_types_user',
        ], GraphQL::getTypes());
    }
}
