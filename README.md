# Laravel Model Properties

Simple model properties implementation.

### Problem

Imagine, you have a Page model with given schema
```php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug');
    $table->text('body');
    $table->timestamps();
});
```

But then, you might decide to add a meta_title property. Or another bunch of properties, that might be useful for just one or two pages. In the end, we will see something like that:
```php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug');
    $table->text('body');
    $table->boolean('has_halloween_theme');
    $table->string('meta_title');
    $table->string('meta_description');
    $table->integer('number_of_external_links');
    $table->boolean('export_to_rss');
    ...
    $table->timestamps();
});
```

### Solution

To avoid extending of original table, we can use a one-to-one relation. But you will need to create additional table for every model with Properties.
So, we might just use a polymorphic relation for properties and models.
```php
Schema::create('property_values', function (Blueprint $table) {
    $table->id();
    $table->morphs('entity');
    $table->morphs('property');
    $table->text('value')->nullable();
    $table->timestamps();
});
```

This package is made to simplify this polymorphic relation setup.

## Installation

You can install the package via composer:

```bash
composer require wardenyarn/model-properties
```

## Usage

First we need to publish a properties table and migrate it. 
```bash
php artisan vendor:publish --provider="Wardenyarn\Properties\PropertiesServiceProvider" --tag="migrations"

php artisan migrate
```

Second, we need to add ``HasProperties`` trait and fill protected ``$properties`` property.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wardenyarn\Properties\Models\HasProperties;

class Page extends Model
{
    use HasProperties;

    protected $properties = [
        'meta_title' => [
            'cast' => 'string',
            'default' => 'This is a page',
        ],
        'tags' => [
            'cast' => 'array',
        ],
    ];
}
```

And lastly, execute artisan **properties:fill** command, that will persist properties from
model `$properties` into DB;
```bash
php artisan properties:fill
```

### Access to properties

Just use `$model->properties` attribute to access to Properties object, that contains all the stuff.
```php
$page = Page::first();

echo $page->properties->meta_title;

foreach ($page->properties->tags as $tag) {
	echo $tag;
}
```

### Saving

Set properties values by `$model->properties->property_name` or using `$model->properties->set()` method.
```php
$page = Page::first();

$page->properties->meta_title = 'New meta title';
$page->properties->last_admin_check = date();
// OR
$page->properties->set([
	'meta_title' => 'New meta title',
	'last_admin_check' => date(),
]);

$page->properties->save();
// OR
$page->save();
```

It also works with new model instances:
```php
$page = new Page;
$page->name = 'New page';
$page->properties->meta_title = 'a new page';
$page->save();
```
**Note:** in this case only `$model->save()` method will save properties.

### Cast

Property values will be automatically casted by Laravel.
```php
protected $properties = [
    'last_admin_check' => [
        'cast' => 'datetime',
    ],
];

...

get_class($post->properties->last_admin_check); // Illuminate\Support\Carbon
```

Available casts:
- array
- boolean
- collection
- date
- datetime
- immutable_date
- immutable_datetime
- double
- float
- integer
- real
- string
- timestamp

If you need access to casted properties right after saving, you need to ``fresh()`` model before use.
```php
$page->properties->last_admin_check = date();
$page->properties->save();

gettype($page->properties->last_admin_check); // string

$page->fresh();

gettype($page->properties->last_admin_check); // object Illuminate\Support\Carbon
```


### Default values

You can define a default value for property
```php
protected $properties = [
    'meta_title' => [
        'cast' => 'string',
        'default' => 'Page title',
    ],
];

...

$post->properties->meta_title = null;
$post->save();

echo $post->properties->meta_title; // Page title
```

### Caching
Property values are cached forever with first access to `properties` attribute, so the database load won't be an issue.

Cache will be refreshed with saving of properties or model.
```php
$page->properties->save(); // Clears the cache
$page->save(); // Clears the cache
```

You can always clear all of the Laravel cache by artisan command
```bash
php artisan cache:clear
```

### Config

You can change config after importing it with given artisan command:
```bash
php artisan vendor:publish --provider="Wardenyarn\Properties\PropertiesServiceProvider" --tag="config"
```


## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
