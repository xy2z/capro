#!/bin/bash

# Build capro.phar using Box

# Install composer packages
# Make sure composer dependencies are installed using --no-dev, to avoid symlinks (will break memory)
composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins --no-autoloader || exit

# Compile to phar file
# see `box.json.dist` for configuration
box compile || exit
# box compile --debug || exit

# Todo: Copy build-files into build dir, or just keep them in build dir without gitignore? (gitignore the phar file?)

# Do some simple testing
cd build || exit
php capro.phar --version || exit
php capro.phar -v || exit
php capro.phar where || exit
php capro.phar config || exit

echo ""
echo "Build done!"
# echo "Build done! Dump of phar filed saved to .box_dump/"
