# Getting started

The easiest way to test this is locally (asumming you have PHP installed):

```
git clone git@github.com:BirkAndMe/hashnoderss.git
cd hashnoderss
php -S localhost:8000 router-hack.php
```

__If things isn't working you propably need to install the [PHP XML module](https://www.php.net/manual/en/simplexml.installation.php): `sudo apt install php-xml`__

Now goto `http://localhost:8000/BLOG_HOSTNAME`, in my case [http://localhost:8000/blog.birk-jensen.dk](http://localhost:8000/blog.birk-jensen.dk).
This should return the complete RSS as seen normally, now try to add a filter to the feed: [http://localhost:8000/blog.birk-jensen.dk?tags--name=Drupal](http://localhost:8000/blog.birk-jensen.dk?tags--name=Drupal)

Add a `debug` parameter to the query to view the working data: [http://localhost:8000/blog.birk-jensen.dk?tags--name=Drupal&debug](http://localhost:8000/blog.birk-jensen.dk?tags--name=Drupal&debug).

Note the Graph QL Query (shown when `debug` is active) can be pasted directly into the [Hashnode API playground](https://api.hashnode.com) for easier testing.

## Setting everything up properly

Do not use the [PHP built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php) for anything other than testing. Instead install the script on any webserver supporting PHP.

_Remember to set it up so it redirects everything to index.php._

# How the filters work

Each query parameter in the URL is a filter that is mapped to a property returned from the Hashnode API post query. Go checkout the "docs" on [https://api.hashnode.com](https://api.hashnode.com) for a complete list of supported properties.

## Simple ID check

```
?_id=6298c0135787a911a45d5fcf
```

[_This will only show the post with a specific ID (not really useful in anything other than an example).._](http://localhost:8000/blog.birk-jensen.dk?_id=6298c0135787a911a45d5fcf)

## Nested data checks.

```
?tags--name=Drupal
```

[_This gets all the posts tagged with the Drupal tag._](http://localhost:8000/blog.birk-jensen.dk?tags--name=Drupal)

```
?author--username=BirkAndMe
```

[_Only get posts by a specific user_](http://localhost:8000/blog.birk-jensen.dk?author--username=BirkAndMe)

## Operators

### Equal operator (=)

Not prefixing the value is the same as using the equal operator (remember to urlencode the = if used).

```
?_id=6298c0135787a911a45d5fcf
?_id=%3D6298c0135787a911a45d5fcf
```

[_Without any prefix_](http://localhost:8000/blog.birk-jensen.dk?_id=6298c0135787a911a45d5fcf)

[_With the escaped prefix (same result)_](http://localhost:8000/blog.birk-jensen.dk?_id=%3D6298c0135787a911a45d5fcf)


### Not operator (!)

```
?_id=!6298c0135787a911a45d5fcf
```

[_Get all other posts than the specific ID._](http://localhost:8000/blog.birk-jensen.dk?_id=!6298c0135787a911a45d5fcf)

### Less (<) and Greater (>) than operators.

These operators is most useful on integers, but should also work on strings. I haven't gotten around implementing them for dates yet.

This could be useful for making an "only" popular feed:

```
?totalReactions=>5
```

[_Only posts with more than 5 reactions_](http://localhost:8000/blog.birk-jensen.dk?totalReactions=%3E5)

## Check for multiple values

At the moment only AND is supported, so these all have to be true before the posts is shown.

```
?author--username=BirkAndMe&tags--name[]=Drupal&tags--name[]=!SomeTag
```

[_Only posts tagged with Drupal written by BirkAndMe, and not tagged with SomeTag_](http://localhost:8000/blog.birk-jensen.dk?author--username=BirkAndMe&tags--name[]=Drupal&tags--name[]=!SomeTag)


## Final remark

__Originally made for the [Hashnode](https://hashnode.com) _Build with [Linode](https://linode.com) Hackathon_.__

[Check out the blog post](https://blog.birk-jensen.dk/hashnoderss)
