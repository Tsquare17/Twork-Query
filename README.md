# Twork Query

## A WordPress WP_Query wrapper.

### Installation

- composer require twork/query

### Example Usage:

```php

use Twork\Query\Query;

$query = new Query('custom-post');

$query->author('Jim')
    ->category('tech')
    ->postsPerPage(20);

foreach ($query as $postId) {
    the_title();
}

```

In the above example, a query is created for custom-post post types, where the author is Jim, and the category is tech, with a maximum of 20 posts per page.

Alternatively, $query->fetch() can be used, which returns a generator that wraps the WP loop.
 It will yield either null, or an object, if a class is supplied as an argument.
```php
use Twork\Query\Query;

$query = new Query();

foreach ($query->fetch() as $null) {
    the_title();
}
```

```php
<?php

class Post {
    protected $id;

    public function __construct() {
        $this->id = get_the_ID();
    }

    public function getId() {
        return $this->id;
    }
}

use Twork\Query\Query;

$query = new Query();

foreach ($query->fetch(Post::class) as $post) {
    echo $post->getId();
}
```

---

Arguments that aren't available as methods can be added using addArg, shown in the example below.

```php

$query = new Query('custom-post');

$query->addArg('author_name', 'Jim');

```
