<?php

namespace Twork\Query;

use Generator;
use WP_Query;

/**
 * Class Query
 * @package Twork\Query
 */
class Query
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
        $this->addArg('s', $search);

        return $this;
    }

    /**
     * Add a meta query.
     *
     * @param string|array $args
     *
     * @return Query
     */
    public function metaQuery($args): Query
    {
        if (is_array($args)) {
            $this->args['meta_query'][] = $args;
        } else {
            $this->args['meta_query'] = $args;
        }

        return $this;
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
     * Get the number of posts.
     *
     * @return int
     */
    public function count(): int
    {
        $this->setQuery();

        return $this->query->found_posts;
    }

    /**
     * Get the number of pages of posts.
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

        $this->query->the_post();

        return $this->query->post;
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

    public function setQuery(): void
    {
        $this->query = $this->query ?: new WP_Query($this->args);
    }
}
