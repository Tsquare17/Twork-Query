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
    public function setUp()
    {
        parent::setUp();

        $editor = self::factory()->user->create(['role' => 'editor']);

        wp_set_current_user($editor);
    }

    /** @test */
    public function query_returns_results(): void
    {
        self::factory()->post->create_many(3);

        $query = new Query();

        self::assertSame(3, $query->count());
    }

    /** @test */
    public function can_query_authors_posts(): void
    {
        $user = self::factory()->user->create();
        $otherUser = self::factory()->user->create();

        self::factory()->post->create_many(2, [
            'post_author' => $user,
        ]);

        self::factory()->post->create_many(4, [
            'post_author' => $otherUser,
        ]);

        $query = new Query();
        $query->author($user);

        foreach ($query->fetch() as $null) {
            self::assertSame($user, get_the_author_meta('ID'));
        }

        $query->reset()
              ->author($otherUser);

        foreach ($query->fetch() as $null) {
            self::assertSame($otherUser, get_the_author_meta('ID'));
        }
    }

    /** @test */
    public function can_query_by_category(): void
    {
        $cat = self::factory()->category->create();
        $otherCat = self::factory()->category->create();

        self::factory()->post->create_many(2, [
            'post_category' => [$cat],
        ]);

        self::factory()->post->create_many(4, [
            'post_category' => [$otherCat],
        ]);

        $query = new Query();
        $query->category($cat);

        foreach ($query->fetch() as $null) {
            self::assertSame($cat, get_the_category()[0]->term_taxonomy_id);
        }

        $query->reset()
            ->category($otherCat);

        foreach ($query->fetch() as $null) {
            self::assertSame($otherCat, get_the_category()[0]->term_taxonomy_id);
        }
    }

    /** @test */
    public function can_query_posts_by_search_term(): void
    {
        $searchTerm = '12test20342';
        $otherSearchTerm = '415948082';

        self::factory()->post->create([
             'post_content' => $searchTerm,
         ]);

        self::factory()->post->create_many(4, [
            'post_content' => $otherSearchTerm,
        ]);

        $query = new Query();
        $query->search($searchTerm);

        self::assertSame(1, $query->count());

        $query->reset()
              ->search($otherSearchTerm);

        self::assertSame(4, $query->count());
    }

    /** @test */
    public function can_set_posts_per_page(): void
    {
        self::factory()->post->create_many(9);

        $query = new Query();

        $originalNumberOfPages = $query->pages();

        self::assertEquals(1, $originalNumberOfPages);

        $query->reset()
            ->postsPerPage(3);

        self::assertEquals(3, $query->pages());
    }

    /** @test */
    public function can_query_by_date(): void
    {
        self::factory()->post->create_many(2, [
            'post_date' => '2020-08-12 18:10:08',
        ]);

        self::factory()->post->create_many(3, [
            'post_date' => '2020-06-12 18:10:08',
        ]);

        self::factory()->post->create_many(4, [
            'post_date' => '2019-06-10 18:10:08'
        ]);

        $query = new Query();
        $query->year('2020');

        self::assertSame(5, $query->count());

        $query->reset()
            ->month('08');

        self::assertSame(2, $query->count());

        $query->reset()
            ->month('06');

        self::assertSame(7, $query->count());

        $query->reset()
            ->day('10');

        self::assertSame(4, $query->count());

        $query->reset()
            ->month('06')
            ->year('2020');

        self::assertSame(3, $query->count());
    }

    /** @test */
    public function can_exclude_posts(): void
    {
        $expectedPostIds = self::factory()->post->create_many(3);

        $excludedPostIds = self::factory()->post->create_many(5);

        $query = new Query();

        $query->excluding($excludedPostIds);

        self::assertSame(count($expectedPostIds), $query->count());
    }

    /** @test */
    public function can_query_by_postmeta(): void
    {
        $id = self::factory()->post->create([
            'meta_input' => [
                'key1' => 'value1',
            ],
        ]);

        self::factory()->post->create_many(5, [
            'meta_input' => [
                'key2' => 'value2',
            ]
        ]);

        $query = new Query();

        $query->metaQuery(
            $query->createMetaQuery()
            ->key('key1')
            ->value('value1')
        );

        $queriedPostId = null;
        if ($post = $query->first()) {
            $queriedPostId = $post->ID;
        }

        self::assertSame($id, $queriedPostId);
        self::assertSame(1, $query->count());
    }

    /** @test */
    public function can_build_nested_meta_query(): void
    {
        $id = self::factory()->post->create([
            'meta_input' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ]);

        self::factory()->post->create_many(5, [
            'meta_input' => [
                'key1' => 'value1',
            ]
        ]);

        self::factory()->post->create_many(5, [
            'meta_input' => [
                'key2' => 'value2',
            ]
        ]);

        $query = new Query();

        $query->metaQuery(
            [
                $query->createMetaQuery()
                    ->key('key2')
                    ->value('value2'),
                $query->createMetaQuery()
                    ->key('key1')
                    ->value('value1')
            ]
        );

        $queriedPostId = null;
        if ($post = $query->first()) {
            $queriedPostId = $post->ID;
        }

        self::assertSame($id, $queriedPostId);
        self::assertSame(1, $query->count());
    }

    /** @test */
    public function can_query_by_taxonomy(): void
    {
        register_taxonomy('test_taxonomy', 'post');
        register_taxonomy('other_tax', 'post');

        $term1 = self::factory()->term->create([
            'taxonomy' => 'test_taxonomy',
            'name' => 'term1',
        ]);

        $term2 = self::factory()->term->create([
            'taxonomy' => 'other_tax',
            'name' => 'term2',
        ]);

        $id = self::factory()->post->create([
            'tax_input' => [
                'test_taxonomy' => $term1
            ],
        ]);

        self::factory()->post->create_many(5, [
            'tax_input' => [
                'other_tax' => [
                    $term2,
                ]
            ],
        ]);

        $query = new Query();

        $query->taxQuery(
            $query->createTaxQuery()
            ->taxonomy('test_taxonomy')
            ->field('slug')
            ->terms([$term1])
        );

        $queriedPostId = null;
        if ($post = $query->first()) {
            $queriedPostId = $post->ID;
        }

        self::assertSame($id, $queriedPostId);
        self::assertSame(1, $query->count());
    }

    /** @test */
    public function can_build_nested_tax_query(): void
    {
        register_taxonomy('test_taxonomy', 'post');
        register_taxonomy('other_tax', 'post');
        register_taxonomy('nogo_tax', 'post');

        $term1 = self::factory()->term->create([
            'taxonomy' => 'test_taxonomy',
            'name' => 'term1',
        ]);

        $term2 = self::factory()->term->create([
            'taxonomy' => 'other_tax',
            'name' => 'term2',
        ]);

        $term3 = self::factory()->term->create([
            'taxonomy' => 'nogo_tax',
            'name' => 'term3',
        ]);

        $id1 = self::factory()->post->create([
            'tax_input' => [
                'test_taxonomy' => $term1
            ],
        ]);

        $id2 = self::factory()->post->create([
            'tax_input' => [
                'other_tax' => $term2
            ],
        ]);

        $id3 = self::factory()->post->create([
            'tax_input' => [
                'nogo_tax' => $term3
            ],
        ]);

        $query = new Query();
        $query->field('id');

        $query->taxQuery(
            [
                $query->createTaxQuery()
                    ->taxonomy('test_taxonomy')
                    ->field('slug')
                    ->terms([$term1])
                    ->relation('OR'),
                [
                    $query->createTaxQuery()
                        ->taxonomy('other_tax')
                        ->field('slug')
                        ->terms([$term2])
                        ->relation('OR'),
                ]
            ]
        );

         self::assertContains($id1, $query);
         self::assertContains($id2, $query);
         self::assertSame(2, $query->count());
    }

    /** @test */
    public function can_query_by_post_slugs(): void
    {
        self::factory()->post->create([
            'post_name' => 'foo',
        ]);

        self::factory()->post->create([
            'post_name' => 'bar',
        ]);

        self::factory()->post->create_many(3);

        $query = new Query();

        $query->slug('foo');

        self::assertSame(1, $query->count());

        self::assertSame('foo', $query->first()->post_name);

        $query->reset()
            ->slug(['foo', 'bar']);

        self::assertSame(2, $query->count());
    }

    /** @test */
    public function can_query_posts_in_ids(): void
    {
        $includedId = self::factory()->post->create([
            'post_name' => 'foo',
        ]);

        self::factory()->post->create_many(3);

        $query = new Query();

        $query->id($includedId);

        self::assertSame(1, $query->count());

        self::assertSame($includedId, $query->first()->ID);
    }

    /** @test */
    public function can_exclude_posts_in_ids(): void
    {
        $excludedId = self::factory()->post->create([
            'post_name' => 'bar',
        ]);

        self::factory()->post->create_many(3);

        $query = new Query();

        $query->id($excludedId, false)
            ->field('id');

        self::assertSame(3, $query->count());

        self::assertNotContains($excludedId, $query);
    }

    /** @test */
    public function can_iterate_over_query_object(): void
    {
        self::assertIsIterable(new Query());

        self::factory()->post->create_many(2, [
            'post_title' => 'foo',
        ]);

        $posts = new Query();

        foreach ($posts as $post) {
            self::assertSame('foo', get_the_title());
        }
    }
}
