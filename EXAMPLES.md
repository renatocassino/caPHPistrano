Some examples
---------------

Run assetic command after deploy in Symfony.
-------------

```php
$cap->task('_runAssetic','Running assetic dump.', function() use (&$cap) {
	$this->run('app/controller assetic:dump --no-warmup');
});

$cap->after('deploy',['_runAssetic']);
```

Clear cache after deploy in Symfony2.
-------------

```php
$cap->task('_cacheClear','Running assetic dump.', function() use (&$cap) {
	$this->run('app/controller cache:clear');
});

$cap->after('deploy',['_cacheClear']);
```

Run the doctrine in `ZF2` after deploy.
-------------

```php
$cap->task('_schemaUpdate','Updating database.', function() use (&$cap) {
	$this->run('zftool.phar orm:schema-tool:update --force');
});

$cap->after('deploy',['_schemaUpdate']);
```

Run the doctrine in Symfony2 after deploy.
-------------

```php
$cap->task('_schemaUpdate','Updating database.', function() use (&$cap) {
	$this->run('app/console doctrine:schema:update --force');
});

$cap->after('deploy',['_schemaUpdate']);
```