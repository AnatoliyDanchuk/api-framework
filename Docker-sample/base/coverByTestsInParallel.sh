#!/bin/bash
set -e

cd "$(dirname "$0")"/../..

PHPUNIT=var/vendor/bin/phpunit
PHPUNIT_XML=var/vendor/Framework/Config/Test/phpunit.xml

FASTEST=var/vendor/bin/fastest
PHPUNIT_BOOTSTRAP_FOR_FASTEST=var/vendor/Framework/Test/bootstrapForParallelRunning.php

TESTS_DIR=Test/TestImplementation
COVERAGE_DIR=var/coverage
FASTEST_COVERAGE_DIR=$COVERAGE_DIR/fastest

# clear cache if exists and build before parallel test running
# to avoid issues with parallel warmups.
find $TESTS_DIR/Symfony/SymfonyWarmUpTest.php | \
  XDEBUG_MODE=coverage $FASTEST --process=1 "$PHPUNIT -c $PHPUNIT_XML {} --coverage-php $FASTEST_COVERAGE_DIR/0.cov;"

TESTS=$(find $TESTS_DIR/ -name "*Test.php" ! -name "SymfonyWarmUpTest.php")
TESTS_NUMBER=$(echo "$TESTS" | wc -l)
echo "$TESTS" | XDEBUG_MODE=coverage $FASTEST --process="$TESTS_NUMBER" "$PHPUNIT -c $PHPUNIT_XML --bootstrap=$PHPUNIT_BOOTSTRAP_FOR_FASTEST {} --coverage-php $FASTEST_COVERAGE_DIR/{n}.cov;"

var/vendor/bin/phpcov merge $FASTEST_COVERAGE_DIR --clover $COVERAGE_DIR/clover.xml

rm -r $FASTEST_COVERAGE_DIR
