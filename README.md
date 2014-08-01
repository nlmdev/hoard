Hoard
=====

[![Build Status](https://travis-ci.org/elliotchance/Hoard.svg?branch=master)](https://travis-ci.org/elliotchance/Hoard)

What Is It?
-----------

Hoard is a caching library.

Hoard's goal are:

* To be totally independant from any other framework.
* To be [PSR-6: Cache](https://github.com/php-fig/fig-standards/pull/149) (more
  information [here](https://github.com/Crell/fig-standards/blob/76527139cad588c072beaedc116438e46ba2f70b/proposed/cache.md))
  compliant so that it should work with any modern framework.
* Support multiple adapters for storage (like memcached).
* Support multiple discreet pools, and nested pools.
* Provide a way to automate actions with a script that is easy to use and
  version controlled with the application.
* A command line utility to run these scripts.

Basic Usage
-----------
### Pools are Classes ###

You may follow the PSR-6 standard explicitely for getting and setting items in
the cache. However there are some code requirements for you to be able to
create the physical pool.

Each pool is represented by a concrete class that extends
`\Hoard\AbstractPool`. Nested pools will be class that extend their parent
pool class, **not** `\Hoard\AbstractPool`.

Pool names translate directly and both ways between the class name. With the
following examples (pool name => class name):

* `my_pool` => `MyPool`
* `parent.child_pool` => `Parent\ChildPool`

Pools are located in a specific namespace provided by
`\Hoard\CacheManager::getNamespace()`, which as of the writing of this
document, is `\Hoard\Hoard\Pool`. Extending from the above examples;

* `my_pool` => `\HoardPool\MyPool`
* `parent.child_pool` => `\HoardPool\Parent\ChildPool`

**Never instantiate the classes themselfs. You must use the `CacheManager` to
retrieve the pool instances.**

    try {
        $pool = \Hoard\CacheManager::getPool('my.pool');
    }
    catch(\Hoard\NoSuchPoolException $e) {
        // deal with this appropriately
    }

### Storing and Retrieving ###

Once you have the pool instance (from the previous example), you can:

    $item = $pool->getItem('key');
    if($item->isHit()) {
        echo "Got: {$item->get()}";
    }
    else {
        echo "Item {$item->getKey()} is not in the cache.";
    }

Regardless of whether the item was stored in the cache previously, you save
data to the cache the same way:

    $item->set('myvalue')

There is an optional second argument for `set()` that allows you to specify a
expiry time:

These will never expire (default):

    $item->set('myvalue');
    $item->set('myvalue', null);

This will expire in 1 hour from now:

    $item->set('myvalue', 3600);

This will expire at the specific timestamp:

    $item->set('myvalue', new \DateTime('4th March 2015'));

### Clearing Cache ###

To delete a single item from a pool:

    $pool->getItem('key')->delete();

You may want to drop an entire pool:

    $pool->clear();

Using a Cache Script
--------------------

A hoard script is just a text file (the extension is not important, although
`.txt` is the easiest to use) which specifies targets and commands that can be
run from a command line utility.

The syntax is similar to a Makefile, targets have dependencies, except that
indentation can be any white space before a command.

Run targets like you would a Makefile, through the `./g` script:

    ./g hoard mytarget

### Syntax ###

Pool names are important because they link directly to concrete classes, trying
to perform any action on a pool that doesn't exist will result in an exception
(the script will stop execution and return a non 0 exit code).

Drop an entire pool (and all of its child pools):

    drop my.pool_name

Drop individual keys inside a pool:

    drop my.pool:key1,key2,key3

Comments are any line, or part of a line that comes after a `#`:

    # this is a comment
    drop my.pool_name # this is also fine

### Full Example ###

    # my cool script
    safe_drop:
        # this are items that may be safely dropped at any time and should be
        # dropped with any change to the software
        drop page:homepage

    release: safe_drop
        # should be run with every production release

Running the `release` target like this:

    $ ./g hoard release
    Running target 'safe_drop'
      drop page:homepage... Done
    Success.
    Running target 'release'
      Nothing to do.
    Success.

The `./g hoard` command runs the script located at `install-script/hoard.txt`.

Mocking a Pool
--------------

There is a `\Hoard\PoolTestCase` you can use to generate mocked pools that
have prepopulated data. This is easier than trying to do it in other ways.

    class MyTest extends \Hoard\PoolTestCase
    {

        public function testMockingPool()
        {
            // create mock
            $pool = $this->getMockedPool(array(
                'mykey' => 'myvalue'
            ));

            // found item
            $item = $pool->getItem('mykey');
            $this->assertEquals($item->get(), 'myvalue');
            $this->assertTrue($item->isHit());

            // not found item
            $item = $pool->getItem('mykey2');
            $this->assertFalse($item->isHit());
        }

    }
