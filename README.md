Diff Sniffer for Git
====================

[![PHP Version](https://img.shields.io/badge/php-%5E7.2-blue.svg)](https://packagist.org/packages/diff-sniffer/git)
[![Latest Stable Version](https://poser.pugx.org/diff-sniffer/git/v/stable)](https://packagist.org/packages/diff-sniffer/git)
[![Build Status](https://travis-ci.org/diff-sniffer/git.png)](https://travis-ci.org/diff-sniffer/git)
[![Appveyor Build Status](https://ci.appveyor.com/api/projects/status/uv1xbj7l7lupgxta?svg=true)](https://ci.appveyor.com/project/morozov/git)

This tool allows you to use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) as a pre-commit hook. The main difference from [existing solutions](https://github.com/s0enke/git-hooks/blob/master/phpcs-pre-commit/pre-commit) that this one validates only changed lines of code but not the whole source tree.

Installation
------------

Diff Sniffer is already built as a PHAR package. All you need is download it and install as a pre-commit hook.
```
$ wget https://github.com/diff-sniffer/git/releases/download/0.3.2/pre-commit.phar
$ chmod +x pre-commit.phar
$ mv pre-commit.phar /path/to/repo/.git/hooks/pre-commit
```

Alternatively, you can install the hook globally for a user (see [`man githooks`](https://git-scm.com/docs/githooks)):
```
$ git config --global core.hooksPath '~/.git/hooks' # choose a path if you already haven't
$ chmod +x pre-commit.phar
$ mv pre-commit.phar ~/.git/hooks/pre-commit
```

You can also install Diff Sniffer manually:

```
$ git clone git@github.com:diff-sniffer/git.git
$ cd diff-sniffer-pre-commit
$ composer install
$ bin/pre-commit --version
```

Continuous integration mode
---------------------------

Diff Sniffer for Git can also run on a CI server and validate pull requests. For example, on Travis CI:
```
$ wget https://github.com/diff-sniffer/git/releases/download/0.3.2/git-phpcs.phar
$ php git-phpcs.phar origin/$TRAVIS_BRANCH...$TRAVIS_PULL_REQUEST_SHA
```

Configuration
-------------

By default, the PHAR distribution uses the PSR2 coding standard. The configuration may be overridden by creating [configuration file](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file) file in the project root.
