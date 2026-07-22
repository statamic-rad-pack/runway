<?php

namespace StatamicRadPack\Runway\Tests;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Statamic\Routing\ResolveRedirect;
use StatamicRadPack\Runway\ResourceLinkType;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;

class ResourceLinkTypeTest extends TestCase
{
    #[Test]
    public function it_registers_a_link_type_for_resources_with_routing()
    {
        $this->assertArrayHasKey('runway_post', Link::types());
        $this->assertInstanceOf(ResourceLinkType::class, Link::types()['runway_post']);
    }

    #[Test]
    public function it_doesnt_register_link_types_for_resources_without_routing()
    {
        $this->assertArrayNotHasKey('runway_author', Link::types());
        $this->assertArrayNotHasKey('runway_user', Link::types());
    }

    #[Test]
    public function its_title_is_the_resources_singular_name()
    {
        $this->assertEquals('Post', Link::resolveType('runway_post')->title());
    }

    #[Test]
    public function its_icon_is_the_resources_icon()
    {
        $this->assertEquals(
            Runway::findResource('post')->icon(),
            Link::resolveType('runway_post')->icon()
        );
    }

    #[Test]
    public function it_returns_a_belongs_to_fieldtype_config()
    {
        $fieldtype = Link::resolveType('runway_post')->fieldtype(new Field('link', []));

        $this->assertEquals([
            'type' => 'belongs_to',
            'resource' => 'post',
            'max_items' => 1,
            'mode' => 'default',
        ], $fieldtype);
    }

    #[Test]
    public function it_resolves_a_stored_id_to_its_model()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/posts/'.$post->slug]);

        $resolved = Link::resolveType('runway_post')->resolve($post->id);

        $this->assertTrue($resolved->is($post));
        $this->assertEquals('/posts/'.$post->slug, $resolved->url());
    }

    #[Test]
    public function it_resolves_null_when_the_model_doesnt_exist()
    {
        $this->assertNull(Link::resolveType('runway_post')->resolve('does-not-exist'));
    }

    #[Test]
    public function it_resolves_a_stored_value_to_a_url_via_resolve_redirect()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/posts/'.$post->slug]);

        $resolved = (new ResolveRedirect)("runway_post::{$post->id}", $post);

        $this->assertEquals('/posts/'.$post->slug, $resolved);
    }
}
