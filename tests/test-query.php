<?php

namespace Twork\Tests;

use WP_UnitTestCase;
use Twork\Query\Query;

/**
 * Class QueryTest
 *
 * Query test case.
 *
 * @package Twork
 */
class QueryTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function query_returns_results(): void
    {
        $this->factory->post->create_many(3);

        $query = new Query();

        $this->assertSame(3, $query->count());
    }

    /** @test */
    public function can_query_authors_posts(): void
    {
        $user = $this->factory->user->create();
        $otherUser = $this->factory->user->create();

        $this->factory->post->create_many(2, [
            'post_author' => $user,
        ]);

        $this->factory->post->create_many(4, [
            'post_author' => $otherUser,
        ]);

        $query = new Query();
        $query->author($user);

        foreach ($query->fetch() as $null) {
            $this->assertSame($user, get_the_author_meta('ID'));
        }
    }

    /** @test */
    public function can_query_by_category(): void
    {
        $cat = $this->factory->category->create();
        $otherCat = $this->factory->category->create();

        $this->factory->post->create_many(2, [
            'post_category' => [$cat],
        ]);

        $posts2 = $this->factory->post->create_many(4, [
            'post_category' => [$otherCat],
        ]);

        $query = new Query();
        $query->category($cat);

        foreach ($query->fetch() as $null) {
            $this->assertSame($cat, get_the_category()[0]->term_taxonomy_id);
        }

        $query->reset();

        $query->category($otherCat);

        foreach ($query->fetch() as $null) {
            $this->assertSame($otherCat, get_the_category()[0]->term_taxonomy_id);
        }
    }

    /** @test */
    public function can_query_posts_by_search_term(): void
    {
        $searchTerm = '12test20342';
        $otherSearchTerm = '415948082';

        $this->factory->post->create([
             'post_content' => $searchTerm,
         ]);

        $this->factory->post->create_many(4, [
            'post_content' => $otherSearchTerm,
        ]);

        $query = new Query();
        $query->search($searchTerm);

        $this->assertSame(1, $query->count());

        $query->reset();

        $query->search($otherSearchTerm);

        $this->assertSame(4, $query->count());
    }

    /** @test */
    public function can_set_posts_per_page(): void
    {
        $this->factory->post->create_many(9);

        $query = new Query();

        $originalNumberOfPages = $query->pages();

        $this->assertEquals(1, $originalNumberOfPages);

        $query->reset();
        $query->addArg('posts_per_page', 3);

        $this->assertNotSame($originalNumberOfPages, $query->pages());
    }
}
