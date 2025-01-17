help:																			## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:																		## install all dependencies for a development environment
	composer install

unit-tests:																		## run phpunit
	XDEBUG_MODE=coverage ./vendor/bin/phpunit -c phpunit.xml --coverage-html tests/coverage/ --coverage-filter src/

code-style:																		## run phpcs
	./vendor/bin/phpcs --basepath=. --standard=phpcs.xml

static-analysis:																## run static analysis checks
	./vendor/bin/phpstan --configuration=phpstan.neon --memory-limit=-1

check: code-style static-analysis unit-tests
