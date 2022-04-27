$(DEBUG).SILENT: ;       # no need for @, DEBUG=yes make ... disable silence
.EXPORT_ALL_VARIABLES: ; # send all vars to shell
.NOTPARALLEL: ;          # wait for target to finish
.ONESHELL: ;             # when a target is built all lines of the recipe will be given to a single invocation
.SUFFIXES: ;             # skip suffix discovery
.DEFAULT_GOAL = all      # Run make "all" by default

PHP_MATRIX := 7.2 7.3 7.4 8.1
DOCKER := docker
COMPOSER_HOME := $(HOME)/.composer
DOCKER_RUN := $(DOCKER) run \
	--interactive --tty --rm \
	--user 1000:1000 \
	--env COMPOSER_HOME=/tmp \
	--volume $(COMPOSER_HOME):/tmp \
	--volume $(PWD):/app \
	--workdir /app

.PHONY: all
all: $(addprefix phpunit-,$(PHP_MATRIX))

.PHONY: phpunit-%
phpunit-%: info-% update-%
	$(DOCKER_RUN) php:$*-cli-alpine vendor/bin/phpunit $(CMD_ARGS)
	# composer config --unset platform

.PHONY: info-%
info-%:
	echo "---------------------------\r\nPHP-$*\r\n"

.PHONY: update-%
update-%: platform-%
	$(DOCKER_RUN) composer update

.PHONY: platform-%
platform-%:
	$(DOCKER_RUN) composer config platform.php $*
