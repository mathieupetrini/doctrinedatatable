#!/bin/bash

# Script origin : https://docs.gitlab.com/ee/ci/examples/php.html

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git -yqq
apt-get install wget -yqq

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-install pdo_mysql