#!/bin/sh

# Run unit tests inside of docker
cd /code/vendor/erdiko/authorize/tests/
phpunit AllTests
