#!/bin/bash

# Build capro.phar using Box

# Install composer packages
# Make sure composer dependencies are installed using --no-dev, to avoid symlinks (will break memory)
composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins --no-autoloader || exit

# Compile to phar file
# see `box.json.dist` for configuration
box compile --debug || exit



# Do some simple testing
cd .box_dump || exit
php capro --version || exit
php capro -v || exit
php capro where || exit
php capro config || exit

echo ""
echo "Build done! Dump of phar filed saved to .box_dump/"
