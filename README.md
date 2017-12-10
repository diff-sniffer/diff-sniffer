Diff Sniffer for Git
====================

[![PHP Version](https://img.shields.io/badge/php-%5E7.1-blue.svg)](https://packagist.org/packages/diff-sniffer/git)
[![Latest Stable Version](https://poser.pugx.org/diff-sniffer/git/v/stable)](https://packagist.org/packages/diff-sniffer/git)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/diff-sniffer/git/badges/quality-score.png)](https://scrutinizer-ci.com/g/diff-sniffer/git/)
[![Code Coverage](https://scrutinizer-ci.com/g/diff-sniffer/git/badges/coverage.png)](https://scrutinizer-ci.com/g/diff-sniffer/git/)
[![Build Status](https://travis-ci.org/diff-sniffer/git.png)](https://travis-ci.org/diff-sniffer/git)

This tool allows you using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) as a pre-commit hook. The main difference from [existing solutions](https://github.com/s0enke/git-hooks/blob/master/phpcs-pre-commit/pre-commit) that this one validates only changed lines of code but not the whole source tree.

Installation
------------

Diff Sniffer is already built as a PHAR package. All you need is download it and install as a pre-commit hook.
```
$ wget https://github.com/diff-sniffer/git/releases/download/3.1.1/pre-commit.phar
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

Configuration
-------------

By default, the PHAR distribution uses the PSR2 coding standard. The configuration may be overridden by creating [configuration file](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file) file in the project root.
