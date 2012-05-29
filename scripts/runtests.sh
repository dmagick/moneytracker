#!/bin/sh

extra=""
if [ "x$1" != "x" ]; then
    extra="$1"
fi

set -x
phpunit --coverage-html testhtml tests/index.php $extra

