Diff Sniffer Core component
===========================

[![PHP Version](https://img.shields.io/badge/php-%5E7.2-blue.svg)](https://packagist.org/packages/diff-sniffer/core)
[![Latest Stable Version](https://poser.pugx.org/diff-sniffer/core/v/stable)](https://packagist.org/packages/diff-sniffer/core)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/diff-sniffer/core/badges/quality-score.png)](https://scrutinizer-ci.com/g/diff-sniffer/core/)
[![Code Coverage](https://scrutinizer-ci.com/g/diff-sniffer/core/badges/coverage.png)](https://scrutinizer-ci.com/g/diff-sniffer/core/)
[![Travis CI Build Status](https://travis-ci.org/diff-sniffer/core.png)](https://travis-ci.org/diff-sniffer/core)
[![AppVeyor Build status](https://ci.appveyor.com/api/projects/status/fa9mr4yg36pf1kgc?svg=true)](https://ci.appveyor.com/project/diff-sniffer/core)

This is a tool that allows validation of coding standards only for changed lines but not the whole file.

This is not a working application. It provides `DiffSniffer\Changeset` interface that should be implemented in order to accomplish some results.

See existing implementations:
* [diff-sniffer/git](https://github.com/diff-sniffer/git): Git command line implementation
