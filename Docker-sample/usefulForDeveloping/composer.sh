#!/bin/bash
set -e

bash "$(dirname "$0")"/../base/docker-compose.env.sh dev \
    exec \
        --workdir=/var/www/Framework/Config/composer \
        php_application \
        composer "$1"
