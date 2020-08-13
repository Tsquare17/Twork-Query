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

        $query->reset()
              ->author($otherUser);

        foreach ($query->fetch() as $null) {
            $this->assertSame($otherUser, get_the_author_meta('ID'));
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

        $this->factory->post->create_many(4, [
            'post_category' => [$otherCat],
        ]);

        $query = new Query();
        $query->category($cat);

        foreach ($query->fetch() as $null) {
            $this->assertSame($cat, get_the_category()[0]->term_taxonomy_id);
        }

        $query->reset()
            ->category($otherCat);

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

        $query->reset()
              ->search($otherSearchTerm);

        $this->assertSame(4, $query->count());
    }

    /** @test */
    public function can_set_posts_per_page(): void
    {
        $this->factory->post->create_many(9);

        $query = new Query();

        $originalNumberOfPages = $query->pages();

        $this->assertEquals(1, $originalNumberOfPages);

        $query->reset()
            ->postsPerPage(3);

        $this->assertEquals(3, $query->pages());
    }

    /** @test */
    public function can_query_by_date(): void
    {
        $this->factory->post->create_many(2, [
            'post_date' => '2020-08-12 18:10:08',
        ]);

        $this->factory->post->create_many(3, [
            'post_date' => '2020-06-12 18:10:08',
        ]);

        $this->factory->post->create_many(4, [
            'post_date' => '2019-06-10 18:10:08'
        ]);

        $query = new Query();
        $query->year('2020');

        $this->assertSame(5, $query->count());

        $query->reset()
            ->month('08');

        $this->assertSame(2, $query->count());

        $query->reset()
            ->month('06');

        $this->assertSame(7, $query->count());

        $query->reset()
            ->day('10');

        $this->assertSame(4, $query->count());

        $query->reset()
            ->month('06')
            ->year('2020');

        $this->assertSame(3, $query->count());
    }
}
