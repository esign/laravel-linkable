# Dynamically link Laravel models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-linkable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-linkable)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-linkable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-linkable)
![GitHub Actions](https://github.com/esign/laravel-linkable/actions/workflows/main.yml/badge.svg)

This package allows you to add a dynamic link to a Laravel model. This dynamic link may be a reference to another model or could also be an external URL. In essence, this package is just a Laravel relationship with some extra utility methods.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-linkable
```

## Usage

### Preparing your model
To make a model have a dynamic link you may add the `HasDynamicLink` trait.

```php
use Esign\Linkable\Concerns\HasDynamicLink;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasDynamicLink;
}
```

Your database structure should look like the following:

```php
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->string('dynamic_link_type')->nullable();
    $table->string('dynamic_link_url')->nullable();
    $table->string('dynamic_link_linkable_model')->nullable();
});
```

#### Internal links
In case you set the `dynamic_link_type` as `internal` the `dynamic_link_linkable_model` field will be used.
In order to know where a internal dynamic link should direct to you may implement `LinkableUrlContract` on the related model:
```php
use Esign\Linkable\Contracts\LinkableUrlContract;

class Post extends Model implements LinkableUrlContract
{
    public function linkableUrl(): ?string
    {
        return "http://localhost/posts/{$this->id}";
    }
}
```

#### External links
In case you set the `dynamic_link_type` as `external` the `dynamic_link_url` field will be used.
The value of this field should be a valid URL, e.g. `https://www.example.com`.

### Storing linkables
Instead of using a regular `MorphTo` relation, this package ships with a `SingleColumnMorphTo` relation.
Some CMS, including our own, do not allow for morphable relations based on two columns, e.g. `dynamic_link_linkable_type` and `dynamic_link_linkable_id`.
The `SingleColumnMorphTo` combines both the type and id fields into a single column, e.g. `dynamic_link_linkable_model`.
The value for this single column is stored in the `{model}:{id}` format, e.g. `post:1`.
You're able to use both a fully qualified class name or a value from your application's [morph map](https://laravel.com/docs/9.x/eloquent-relationships#custom-polymorphic-types), just like a regular morphTo relation.

> **Note**
> This approach is not ideal and more complex queries using this relationship may not work as expected. In case you're able to you may overwrite the `dynamicLinkLinkable` relation to use Laravel's default `MorphTo` relationship.

### Linkables overview
To create an overview of all possible linkables you can create a [MySQL view](https://dev.mysql.com/doc/refman/5.7/en/create-view.html) that creates a [union](https://dev.mysql.com/doc/refman/5.7/en/union.html) of all possible models that can be linked to:

```php
DB::statement('
    CREATE OR REPLACE VIEW linkables AS
    SELECT
        CONCAT("post:", id) AS id,
        "post" AS linkable_type,
        id AS linkable_id,
        CONCAT("Post - ", title) AS label
    FROM posts
    UNION
    SELECT
        CONCAT("comment:", id) AS id,
        "comment" AS linkable_type,
        id AS linkable_id,
        CONCAT("Comment - ", title) AS label
    FROM comments
');
```
This would create the following output:
| id | linkable_type | linkable_id | label |
| --- | --- | --- | --- |
| post:1 | post | 1 | My First Post |
| comment:1 | comment | 1 | My First Comment |
### Rendering dynamic links
This package ships with a view component that will help you render both internal and external links:
```php
use Esign\Linkable\Concerns\HasDynamicLink;
use App\Models\Post;
use App\Models\MenuItem;

$post = Post::create();
$menuItemInternal = MenuItem::create([
    'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
    'dynamic_link_url' => null,
    'dynamic_link_linkable_model' => "post:{$post->id}",
]);

$menuItemExternal = MenuItem::create([
    'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
    'dynamic_link_url' => 'https://www.esign.eu',
    'dynamic_link_linkable_model' => null,
]);
```
The view component will render an `<a>` tag, when a model of the type `external` is given, the `target="_blank"` and `rel="noopener"` attributes will be applied.
```blade
<x-linkable-dynamic-link :model="$menuItemInternal">Esign</x-linkable-dynamic-link>
<a href="https://www.esign.eu/posts/1">Esign</a>
```
```blade
<x-linkable-dynamic-link :model="$menuItemExternal">Esign</x-linkable-dynamic-link>
<a href="https://www.esign.eu" target="_blank" rel="noopener">Esign</a>
```


### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
