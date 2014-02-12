Diff Sniffer Pre-Commit Hook
============================

This tool allows you using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) as a pre-commit hook. The main difference from [existing solutions](https://github.com/s0enke/git-hooks/blob/master/phpcs-pre-commit/pre-commit) that this one validates only changed lines of code but not the whole source tree.

Installation
------------

Diff Sniffer is already built as PHAR-package with a few predefined configurations. All you need is download it and install as a pre-commit hook.
```
$ wget https://www.dropbox.com/s/cukhmq8wrsge5zr/pre-commit-psr2.phar
$ chmod +x pre-commit-psr2.phar
$ mv pre-commit-psr2.phar /path/to/repo/.git/hooks/pre-commit
```

Additionally there are builds [without predefined standard](https://www.dropbox.com/s/dbj05cecvdued49/pre-commit.phar) (PHP_CodeSniffer uses PEAD by default) and with [SugarCRM](https://www.dropbox.com/s/01e8cchopv70fcw/pull-request-sugarcrm.phar) coding standard.

You can also install and configure Diff Sniffer manually.

```
$ git clone git@github.com:morozov/diff-sniffer-pre-commit.git
$ composer update
$ ln -s /path/to/diff-sniffer-pre-commit/bin/pre-commit /path/to/repo/.git/hooks/
```

Configuration
-------------

The coding standard used by default may be defined in config.php (doesn't exist by default). Similarly to PHP_CodeSniffer, both embedded and custom standards may be used.
```php
<?php

return array(
    '--standard=PSR-2',
);
```

or

```php
<?php

return array(
    '--standard=/path/to/custom/standard',
);
```

If you want to reuse the same installation of Diff Sniffer with different standards, you can pass `--standard` as command line option. You should use shell script wrapper as pre-commit hook instead of symlink then.
```bash
#!/bin/bash

/path/to/diff-sniffer-pre-commit/bin/pre-commit --standard=PSR-2
```
