vendor: composer.lock
	composer install
	touch $@

composer.lock: composer.json
	composer install
	touch -c $@

test: vendor
	vendor/bin/phpcs
	vendor/bin/phpstan a
	vendor/bin/phpunit --color

coverage: vendor
	$(eval TMPDIR=$(shell mktemp -d))
	vendor/bin/phpunit --coverage-html=$(TMPDIR)
	xdg-open $(TMPDIR)/index.html

.PHONY: build
build: build/diff-sniffer.phar

build/diff-sniffer.phar: vendor $(shell find bin/ src/ -type f) box.json.dist .git/HEAD
	box compile
	touch -c $@
