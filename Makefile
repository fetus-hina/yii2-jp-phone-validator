.PHONY: all
all: test

.PHONY: test
test: vendor check-style
	vendor/bin/phpunit

.PHONY: check-style
check-style:
	find . \( -type d \( -name '.git' -or -name 'vendor' -or -name 'runtime' \) -prune \) -or \( -type f -name '*.php' -print \) | xargs -n 1 php -l
	vendor/bin/phpcs

.PHONY: fix-style
fix-style:
	vendor/bin/phpcbf

.PHONY: clean
clean:
	rm -rf vendor composer.phar

composer.lock: composer.json composer.phar
	./composer.phar update -vvv
	touch $@

vendor: composer.lock composer.phar
	./composer.phar install -vvv
	touch $@

composer.phar:
	curl -sS https://getcomposer.org/installer | php
	touch -r composer.json $@
