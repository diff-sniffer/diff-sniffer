install:
	composer install
test:
	vendor/bin/phpunit --color
	vendor/bin/phpstan analyze -l7 src tests
	vendor/bin/phpcs
coverage:
	$(eval TMPDIR=$(shell mktemp -d))
	vendor/bin/phpunit --coverage-html=$(TMPDIR)
	xdg-open $(TMPDIR)/index.html
