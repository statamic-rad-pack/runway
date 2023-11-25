<?php

namespace DoubleThreeDigital\Runway\Tests\Support;

use DoubleThreeDigital\Runway\Support\Json;
use DoubleThreeDigital\Runway\Tests\TestCase;

class JsonTest extends TestCase
{
    /** @test */
    public function it_determines_if_array_is_json()
    {
        $isJson = Json::isJson([
            'foo' => 'bar',
        ]);

        $this->assertFalse($isJson);
    }

    /** @test */
    public function it_determines_if_object_is_json()
    {
        $isJson = Json::isJson((object) [
            'foo' => 'bar',
        ]);

        $this->assertFalse($isJson);
    }

    /** @test */
    public function it_determines_if_json_string_is_json()
    {
        $isJson = Json::isJson(json_encode([
            'foo' => 'bar',
        ]));

        $this->assertTrue($isJson);
    }
}
