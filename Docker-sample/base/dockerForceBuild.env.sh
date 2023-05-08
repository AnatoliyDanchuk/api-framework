#!/bin/bash
set -e

bash "$(dirname "$0")"/docker-compose.env.sh \
    "$1" \
    build \
      --no-cache

bash "$(dirname "$0")"/docker-compose.env.sh \
    "$1" \
    up \
      --remove-orphans \
      -d
