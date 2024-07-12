<?php

namespace StatamicRadPack\Runway\Tests\Support;

use StatamicRadPack\Runway\Support\Json;
use StatamicRadPack\Runway\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class JsonTest extends TestCase
{
    #[Test]
    public function it_determines_if_array_is_json()
    {
        $isJson = Json::isJson([
            'foo' => 'bar',
        ]);

        $this->assertFalse($isJson);
    }

    #[Test]
    public function it_determines_if_object_is_json()
    {
        $isJson = Json::isJson((object) [
            'foo' => 'bar',
        ]);

        $this->assertFalse($isJson);
    }

    #[Test]
    public function it_determines_if_json_string_is_json()
    {
        $isJson = Json::isJson(json_encode([
            'foo' => 'bar',
        ]));

        $this->assertTrue($isJson);
    }
}
