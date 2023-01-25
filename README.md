# Symfony 1

Tactics Fork of Symfony 1 - PHP 7.4 compatible.

## Install

Make sure to add this to the *"repositories"* key in your ```composer.json```
since this is a private package hosted on our own Composer repository generator Satis.

```bash
"repositories": [
    {
        "type": "composer",
        "url": "https://satis.tactics.be"
    }
]
````

Then run the following command

``` bash
$ composer require tactics/symfony
```

## Dependencies

When this package requires a new dependency make sure to install it through the docker container.
That way we can make sure the dependency is never out of sync with the php/composer version



