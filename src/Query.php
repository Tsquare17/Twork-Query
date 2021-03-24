<?php

namespace Twork\Query;

use Generator;
use Iterator;
use WP_Query;

/**
 * Class Query
 * @package Twork\Query
 */
class Query implements Iterator
{
    /**
     * @var WP_Query
     */
    protected $query;

    /**
     * @var array WP_Query arguments.
     */
    protected $args;

    /**
     * @var array The original WP_Query arguments.
     */
    protected $originalArgs;

    /**
     * @var string Post type.
     */
    protected $postType;

    /**
     * @var int Number of times to loop through posts.
     */
    protected $loop = 1;

    /**
     * @var MetaQuery[] A set of meta queries.
     */
    protected $metaQueries = [];

    /**
     * @var TaxQuery[] A set of taxonomy queries.
     */
    protected $taxQueries = [];

    /**
     * Query constructor.
     *
     * @param string     $type
     * @param array|null $args
     */
    public function __construct($type = 'post', array $args = null)
    {
        $this->postType = $type;

        $this->args = $args ?: [];

        if (!array_key_exists('post_type', $this->args)) {
            $this->addArg('post_type', $this->postType);
        }

        $this->originalArgs = $this->args;
    }

    /**
     * Set the number of posts per page.
     *
     * @param int $postsPerPage
     *
     * @return Query
     */
    public function postsPerPage($postsPerPage): Query
    {
        $this->addArg('posts_per_page', $postsPerPage);

        return $this;
    }

    /**
     * Set query paged.
     *
     * @param string $queryVar
     *
     * @return Query
     */
    public function paged(string $queryVar): Query
    {
        $this->addArg('paged', get_query_var($queryVar) ? absint(get_query_var($queryVar)) : 1);

        return $this;
    }

    /**
     * Add to the query args.
     *
     * @param string       $key
     * @param string|array $value
     * @param null|string  $parent
     *
     * @return Query
     */
    public function addArg($key, $value, $parent = null): Query
    {
        if (!$parent) {
            $this->args[$key] = $value;
        } else {
            $this->args[$parent][$key] = $value;
        }

        return $this;
    }

    /**
     * Execute the query.
     *
     * @return WP_Query
     */
    public function execute(): WP_Query
    {
        $this->setQuery();

        return $this->query;
    }

    /**
     * Fetch posts.
     *
     * @param string|null $object
     * @param mixed       ...$args
     *
     * @return Generator|null
     */
    public function fetch(string $object = null, ...$args): ?Generator
    {
        $this->setQuery();

        if ($this->query->have_posts()) {
            for ($i = 0; $i < $this->loop; $i++) {
                while ($this->query->have_posts()) {
                    $this->query->the_post();
                    yield $object ? new $object($args) : null;
                }

                wp_reset_postdata();
            }
        }
    }

    /**
     * Reset the query.
     *
     * @return Query
     */
    public function reset(): Query
    {
        $this->args = $this->originalArgs;

        $this->query = null;

        return $this;
    }

    /**
     * Get pagination links.
     *
     * @param int|null $total
     * @param string|null $previousText
     * @param string|null $nextText
     *
     * @return array|string|void
     */
    public function pagination(int $total = null, string $previousText = null, string $nextText = null)
    {
        $args = [
            'total' => $total ?? $this->query->max_num_pages,
        ];

        if ($previousText) {
            $args['prev_text'] = __($previousText, 'twork_query');
        }

        if ($nextText) {
            $args['next_text'] = __($nextText, 'twork_query');
        }

        return paginate_links($args);
    }

    /**
     * Set the category of posts to query.
     *
     * @param $category
     *
     * @return Query
     */
    public function category($category): Query
    {
        if (is_numeric($category)) {
            $this->addArg('cat', $category);
        } else {
            $this->addArg('category_name', $category);
        }

        return $this;
    }

    /**
     * Set a search term.
     *
     * @param $search
     *
     * @return Query
     */
    public function search($search): Query
    {
        if (isset($this->args['s'])) {
            $this->args['s'] .= "+{$search}";

            return $this;
        }

        $this->addArg('s', $search);

        return $this;
    }

    /**
     * Add a meta query.
     *
     * @param MetaQuery $metaQuery
     * @return Query
     */
    public function metaQuery(MetaQuery $metaQuery): Query
    {
        $this->metaQueries[] = $metaQuery;

        return $this;
    }

    /**
     * @return MetaQuery
     */
    public function createMetaQuery(): MetaQuery
    {
        return new MetaQuery();
    }

    /**
     * Add a taxonomy query.
     *
     * @param TaxQuery $taxQuery
     * @return Query
     */
    public function taxQuery(TaxQuery $taxQuery): Query
    {
        $this->taxQueries[] = $taxQuery;

        return $this;
    }

    /**
     * @return TaxQuery
     */
    public function createTaxQuery(): TaxQuery
    {
        return new TaxQuery();
    }

    /**
     * Query for author(s).
     *
     * @param $args
     *
     * @return Query
     */
    public function author($args): Query
    {
        if (is_string($args)) {
            $this->addArg('author_name', $args);
        } elseif (is_int($args)) {
            $this->addArg('author', $args);
        } elseif (is_array($args)) {
            $this->addArg('author', implode(',', $args));
        }

        return $this;
    }

    /**
     * Set the order.
     *
     * @param $order
     *
     * @return Query
     */
    public function order($order): Query
    {
        $this->addArg('order', $order);

        return $this;
    }

    /**
     * Set the parameter by which to order posts.
     *
     * @param $param
     *
     * @return Query
     */
    public function orderBy($param): Query
    {
        $this->addArg('orderby', $param);

        if (!isset($this->args['order'])) {
            $this->addArg('order', 'DESC');
        }

        return $this;
    }

    /**
     * Query posts by status.
     *
     * @param $status
     *
     * @return Query
     */
    public function status($status): Query
    {
        $this->addArg('post_status', $status);

        return $this;
    }

    /**
     * Query posts by year.
     *
     * @param $year
     *
     * @return Query
     */
    public function year($year): Query
    {
        $this->addArg('year', $year, 'date_query');

        return $this;
    }

    /**
     * Query posts by month.
     *
     * @param $month
     *
     * @return Query
     */
    public function month($month): Query
    {
        $this->addArg('month', $month, 'date_query');

        return $this;
    }

    /**
     * Query posts by day.
     *
     * @param $day
     *
     * @return Query
     */
    public function day($day): Query
    {
        $this->addArg('day', $day, 'date_query');

        return $this;
    }

    /**
     * Query posts by slugs.
     *
     * @param $slug
     * @param bool $in
     * @return Query
     */
    public function slug($slug, $in = true): Query
    {
        $this->addArg('post_name__' . $this->inOrNot($in), is_array($slug) ? $slug : [$slug]);

        return $this;
    }

    /**
     * Query posts by ids.
     *
     * @param $id
     * @param bool $in
     * @return $this
     */
    public function id($id, $in = true): Query
    {
        $this->addArg('post__' . $this->inOrNot($in), is_array($id) ? $id : [$id]);

        return $this;
    }

    /**
     * Set the column to return.
     *
     * @param $field
     * @return $this
     */
    public function field($field): Query
    {
        $this->addArg('field', $field);

        return $this;
    }

    /**
     * Compose the appropriate include/exclude string.
     *
     * @param $in
     * @return string
     */
    protected function inOrNot($in): string
    {
        return (!$in ? 'not_' : '') . 'in';
    }

    /**
     * Get the total number of posts.
     *
     * @return int
     */
    public function count(): int
    {
        $this->setQuery();

        return $this->query->found_posts;
    }

    /**
     * Get the number of pages of posts (found posts / posts per page).
     *
     * @return int
     */
    public function pages(): int
    {
        $this->setQuery();

        return $this->query->max_num_pages;
    }

    /**
     * Get the first result.
     *
     * @return mixed
     */
    public function first()
    {
        $this->setQuery();

        if ($this->query->have_posts()) {
            $this->query->the_post();

            return $this->query->post;
        }

        return null;
    }

    /**
     * Exclude posts by an array of ids.
     *
     * @param $postIds
     *
     * @return Query
     */
    public function excluding(array $postIds): Query
    {
        $this->addArg('post__not_in', $postIds);

        return $this;
    }

    /**
     * Set the query property.
     */
    protected function setQuery(): void
    {
        if (!isset($this->args['meta_query'])) {
            $this->buildMetaQueries();
        }

        if (!isset($this->args['tax_query'])) {
            $this->buildTaxQueries();
        }

        $this->query = $this->query ?: new WP_Query($this->args);
    }

    /**
     * Add meta queries to args.
     * TODO: This will need to change to accommodate nesting queries.
     */
    protected function buildMetaQueries(): void
    {
        foreach ($this->metaQueries as $query) {
            if ($query === $this->metaQueries[0]) {
                $this->args['meta_query']['relation'] = $query->getRelation();
            }

            $this->args['meta_query'][] = [
                'key' => $query->getKey(),
                'value' => $query->getValue(),
                'compare' => $query->getCompare(),
                'type' => $query->getType(),
            ];
        }
    }

    /**
     * Add taxonomy queries to args.
     * TODO: This will need to change to accommodate nesting queries.
     */
    protected function buildTaxQueries(): void
    {
        foreach ($this->taxQueries as $query) {
            if ($query === $this->taxQueries[0]) {
                $this->args['relation'] = $query->getRelation();
            }

            $this->args['tax_query'][] = [
                'taxonomy' => $query->getTaxonomy(),
                'field' => $query->getField(),
                'terms' => $query->getTerms(),
                'include_children' => $query->isIncludingChildren(),
            ];
        }
    }

    /**
     * Return the current element
     *
     * @return int
     */
    public function current(): int
    {
        $this->setQuery();

        $this->query->the_post();

        return $this->query->current_post;
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        // Unneeded.
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key(): int
    {
        $this->setQuery();

        return $this->query->current_post;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool|null
     */
    public function valid(): ?bool
    {
        $this->setQuery();

        if ($this->query->have_posts()) {
            return true;
        }

        return null;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind(): void
    {
        $this->setQuery();

        $this->query->rewind_posts();
    }
}
