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

foreach ($query->fetch() as $null) {
    the_title();
}

```

In the above example, a query is created for custom-post post types, where the author is Jim, and the category is tech, with a maximum of 20 posts per page.
 
$query->fetch() returns a generator that wraps the WP loop.

---

Arguments that aren't available as methods can be added using addArg, shown in the example below.

```php

$query = new Query('custom-post');

$query->addArg('author_name', 'Jim');

```
