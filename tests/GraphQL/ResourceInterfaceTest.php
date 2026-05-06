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
        $types = array_keys(GraphQL::getTypes());

        $this->assertContains('runway_graphql_types_author', $types);
        $this->assertContains('runway_graphql_types_post', $types);
        $this->assertContains('runway_graphql_types_user', $types);
        $this->assertContains('Runway_NestedFields_Post_Values', $types);
        $this->assertContains('Runway_NestedFields_Post_ExternalLinks', $types);
    }
}
