# Borsch Cache

A simple PSR-6 and PSR-16 implementation.

## Installation

Via [composer](https://getcomposer.org/) :

```bash
composer require borschphp/cache
```

## Usage

```php
// PSR-16 style
$cache = new Cache(new ArrayCacheItemPool());
$cache->set('foo', 'bar');

$foo = $cache->get('foo', 'default_value');

$cache->delete('foo');
$cache->clear();

// PSR-6 style
$pool = new ArrayCacheItemPool();
$pool->save(new CacheItem('foo', 'bar'));

if ($pool->hasItem('foo')) {
    $foo = $pool->getItem('foo')->get();
    
    $pool->deleteItem('foo');
}

$pool->clear();
```

A `LoggerInterface` instance can be given as a second parameter to the `Cache` class to log errors during cache operations.

## License

The package is licensed under the MIT license.  
See [License File](https://github.com/borschphp/borsch-cache/blob/master/LICENSE.md) for more information.
