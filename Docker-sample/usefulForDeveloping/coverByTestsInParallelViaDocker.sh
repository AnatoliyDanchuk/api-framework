#!/bin/bash
set -e

LOCAL_ROOT="$(dirname "$0")"/../..

bash "$LOCAL_ROOT"/Docker/base/docker-compose.env.sh dev \
    exec \
        --workdir=/var/www \
        php_application \
        bash Docker/base/coverByTestsInParallel.sh
