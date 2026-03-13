# ScandiWeb Ecom Api Test

the goal of this project is demonstrating the ability to accomplish the test tasks by following guidelines and the ability to learn and explore
I tend to document some of my finding that might be interesting or simply needs a revisit or repetition.

### how to check if autoload is working ?

- run `composer dump-autoload -o`
- run health-check for autoloading
  `php -r "require 'vendor/autoload.php'; echo file_exists('vendor/autoload.php') ? 'autoload.php OK' : 'autoload.php MISSING'; echo PHP_EOL; echo class_exists('GraphQL\\\\GraphQL') ? 'Vendor autoload OK' : 'Vendor autoload FAIL'; echo PHP_EOL; echo class_exists('App\\\\Controller\\\\GraphQL') ? 'App PSR-4 OK' : 'App PSR-4 FAIL'; echo PHP_EOL;"`

### Registry Pattern ( needs revisit)

why ?

- its needed to avoid the circular dependencies by instantiating the class/type only once
- improves GraphQl performance ==> 'If your schema has 50 fields that reference the Product type, you still only have one instance of ProductType in memory.'
