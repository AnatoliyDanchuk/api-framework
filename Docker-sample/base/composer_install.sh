#!/bin/bash
set -e

function composer_install {
    php \
      "$(which "composer")" \
        install \
          --prefer-dist \
          --no-ansi \
          --no-interaction \
          --no-progress \
          --classmap-authoritative \
          --no-plugins \
          "$1"
}

cd /var/www/Framework/Config/composer &&
if [[ "$1" == "release" ]] ; then
    composer_install --no-dev
else
    composer_install
fi
