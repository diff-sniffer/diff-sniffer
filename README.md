Diff Sniffer for Git
====================

[![PHP Version](https://img.shields.io/badge/php-%5E7.3-blue.svg)](https://packagist.org/packages/diff-sniffer/diff-sniffer)
[![Latest Stable Version](https://poser.pugx.org/diff-sniffer/diff-sniffer/v/stable)](https://packagist.org/packages/diff-sniffer/diff-sniffer)
![Build Status](https://github.com/diff-sniffer/diff-sniffer/workflows/CI/badge.svg)
[![AppVeyor Build Status](https://ci.appveyor.com/api/projects/status/h4lviqjlte6t1vui?svg=true)](https://ci.appveyor.com/project/morozov/diff-sniffer)
[![Code Coverage](https://codecov.io/gh/diff-sniffer/diff-sniffer/branch/master/graph/badge.svg)](https://codecov.io/gh/diff-sniffer/diff-sniffer)

This tool allows you to use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) as a pre-commit hook. The main difference from [existing solutions](https://github.com/s0enke/git-hooks/blob/master/phpcs-pre-commit/pre-commit) that this one validates only changed lines of code but not the whole source tree.

Installation
------------

Download a PHAR package of the latest release and put it somewhere within your `$PATH`:
```
$ wget https://github.com/diff-sniffer/diff-sniffer/releases/download/0.4.0/diff-sniffer.phar
$ chmod +x diff-sniffer.phar
$ sudo cp diff-sniffer.phar /usr/local/bin/diff-sniffer
```

Create a pre-commit hook in a specific Git repository .
```
$ cd /path/to/repo
$ cat > .git/hooks/pre-commit << 'EOF'
#!/usr/bin/env bash

diff-sniffer --staged "$@"
EOF
```

Alternatively, you can create a global pre-commit hook for your user (see [`man githooks`](https://git-scm.com/docs/githooks)):
```
$ cat > ~/.config/git/hooks/pre-commit << 'EOF'
#!/usr/bin/env bash

diff-sniffer --staged "$@"
EOF
```

You can also install Diff Sniffer manually:

```
$ git clone git@github.com:diff-sniffer/diff-sniffer.git
$ cd diff-sniffer
$ composer install
$ bin/diff-sniffer --version
```

Continuous integration mode
---------------------------

Diff Sniffer can also run on a CI server and validate pull requests. For example, on Travis CI:
```
$ wget https://github.com/diff-sniffer/diff-sniffer/releases/download/0.4.0/diff-sniffer.phar
$ php diff-sniffer.phar origin/$TRAVIS_BRANCH...$TRAVIS_PULL_REQUEST_SHA
```
