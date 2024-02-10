# Contributing to Capro

Pull Requests are welcome! Remember development is done in the `dev` branch, and only phar releases is merged to `master`.

- If you want to work on a completely new feature please make an issue beforehand to discuss if it fits the projects scope.
- Use `php-cs-fixer` to follow the coding standard. This can be called automatically in modern editors on file save.
- Call `vendor\bin\phpstan` to check for errors.
- Make sure it works with all supported PHP versions (see "Requirements" in the README, or composer.json).
- When you test the code you should run "php capro <command>" to make sure you are running the correct script.

If you have any questions or notes about contributing, please ask in [Discussions](https://github.com/xy2z/capro/discussions)


## Build and Release
1. Check `box validate` command to validate the box.json.dist file. (should be part of "build.sh" script, fails if not valid)
1. Make sure the version is bumped in `src/capro_bootstrap.php`
1. Run `composer build` to build the phar file in the `build` dir.
1. Test the `build/capro.phar` file.
1. Run `composer validate` to check the `build/composer.json` file.
1. Overwrite the `build/capro.phar` file in the master branch, commit and push
1. Make a new release on GitHub (make sure the version matches the one in `src/capro_bootstrap.php`)

To get back the development composer packages (phpstan, etc.) run `composer install` again.
