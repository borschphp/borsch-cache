<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Borsch\Cache\Cache;
use Borsch\Cache\CacheItem;
use Borsch\Cache\Pool\InMemoryCacheItemPool;

uses()
    ->beforeEach(function () {
        $this->pool = new InMemoryCacheItemPool();
        $this->pool->save(new CacheItem('foo', 'bar'));
        $this->pool->save(new CacheItem('bar', 'baz'));
    })
    ->in('Unit/InMemoryCacheItemPoolTest.php');

uses()
    ->beforeEach(function () {
        $this->cache = new Cache(new InMemoryCacheItemPool());
        $this->cache->set('foo', 'bar');
        $this->cache->set('bar', 'baz');
    })
    ->in('Unit/CacheTest.php');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
