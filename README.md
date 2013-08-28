Diff Sniffer Core component
===========================

This is a tool that allows validation of coding standards only for changed lines but not the whole file.

This is not a working application. It provides `DiffSniffer\Changeset` interface that should be implemented in order to accomplish some results.

See existing implementations:
* [diff-sniffer-pre-commit](https://github.com/morozov/diff-sniffer-pre-commit): Git pre-commit hook
* [diff-sniffer-pull-request](https://github.com/morozov/diff-sniffer-pull-request): GitHub pull request validator
