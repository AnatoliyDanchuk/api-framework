#!/bin/bash
set -e

docker-compose \
    --file ../base/docker-compose.yml \
    --file docker-compose."$1".yml \
    "${@: 2}"
