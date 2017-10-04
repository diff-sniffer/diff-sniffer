install:
	composer install
test:
	vendor/bin/phpunit --color
coverage:
	$(eval TMPDIR=$(shell mktemp -d))
	vendor/bin/phpunit --coverage-html=$(TMPDIR)
	xdg-open $(TMPDIR)/index.html
