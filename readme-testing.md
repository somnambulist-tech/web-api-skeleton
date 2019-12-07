# Testing

This project has been configured to use PHPUnit 8.X, both via the Symfony bridge and in the
composer.json file. If you require an alternative version be sure to update the settings. 

__Note:__ by default xdebug is not installed in the docker containers. If you require code
coverage support be sure to modify the Dockerfiles and add `php7-pecl-xdebug`.

The following helpers are available by default in the `tests/Support` folder:

 * BootKernel - auto-boot the `App\Kernel` on test setUp
 * BootTestClient - auto-boot the test Client service on test setUp (use instead of BootKernel)
 * DoctrineHelper - accessors for getting Doctrine EntityManager or a configured EntityLocator
 * GenerateRouteTo - use named routes conveniently in tests
 * MakeJsonRequestTo - wrap SF Client to make a JSON request to a resource (requires `BootTestClient`)
 * UseObjectFactoryHelper - wrapper around faker to provide test objects

Tests should incorporate both domain tests including object assignment / event raising tests
and functional tests of any delivery mechanism.

Starting with Symfony 4.4, the kernel should not be pre-booted before fetching a client and in
Symfony 5.0 this will raise an exception. Sometimes it is necessary to pre-boot the kernel to
perform test setup. In these cases, e.g. to access the router before running tests, you can
use the `BootTestClient` trait instead. This will boot the kernel via the `static::createClient`
method. If you need access to the client, use `static::getClient()` in place of `createClient`.
Note: this should only be needed when using `WebTestCase`. Do not mix both `BootKernel` and
`BootTestClient` - they are mutually exclusive.

The ObjectFactoryHelper adds a simple way of adding pre-configured domain objects for testing.
You can define your own factory classes and add them in the constructor of the helper. These
can be then be accessed by adding the trait to your test case:

For example; add the `ObjectFactory` to the helper and then use it in a test case:

```php
<?php
class ObjectFactoryHelper
{

    public function __construct(string $locale = Factory::DEFAULT_LOCALE)
    {
        $this->faker     = Factory::create($locale);
        $this->factories = [
            'object' => new ObjectFactory(),
        ];
    }
}
```

The test case:
```php
<?php
use App\Tests\Support\Behaviours\UseObjectFactoryHelper;
use PHPUnit\Framework\TestCase;

class MyUnitTest extends TestCase
{
    use UseObjectFactoryHelper;

    public function testDomainObject()
    {
        $entity = $this->factory->from('object')->makeThing();
        // or because of __get magic...
        $entity = $this->factory->object->makeThing();
    }
}
```

## Testing in Docker

A custom `docker-compose-test.yml` file is provided that provides just enough services to run
the test suite. You should customise this if you need other services.

Tests can be run in docker by:

 * `docker-compose -f docker-compose-test.yml up -d`
 * `bin/dc-phpunit`

`dc-phpunit` wraps the underlying phpunit script and runs it within the current app container
instance. This is configured in the `.env` file. All standard options can be passed through
to the docker phpunit e.g. `--group=some_group`.

To shutdown the test containers: `docker-compose -f docker-compose-test.yml down`

__Note:__ when using different configuration files, be sure to provide the `-f <file>` before
the `docker-compose` command.

## Testing from PhpStorm

To setup PhpStorm to run your local Docker test suite, follow these steps:

 * Open the Preferences and go to `Build, Execution, Deployment` -> `Docker`
 * Select Docker
 * Click + to add a new Docker service
 * Select your Docker option e.g. `Docker for Mac`
 * Save the changes

See: [Docker Settings](https://www.jetbrains.com/help/phpstorm/2019.2/docker-connection-settings.html)
for more help on setting up Docker connections.

To configure the test environment requires setting up a remote interpreter, a PHPUnit interpreter and then a
project runtime configuration. This is done as follows:

 * Open Preferences and go to `Languages & Framework` -> `PHP`
 * Add a new interpreter by clicking the `...` next to the interpreter
 * From interpreter dialogue, select the `+` to add a new interpreter
 * Select `From Docker, Vagrant`
 * Select `Docker Compose` (note: docker should be running and accessible)
 * Select the Docker service (localhost or whatever you called it)
 * Set the docker-compose file to the `docker-compose-test.yml` file
 * Select the `app` service (or the PHP container if you renamed it)
 * Add an environment var: `APP_ENV=test` to ensure the env is set to test
 * Click `OK`
 * Wait for PhpStorm to validate the interpreter
 * Click `OK`

 * Navigate to `Languages & Frameworks` -> `PHP` -> `Test Frameworks`
 * Click `+` to add a new test runner
 * Select `PHPUnit by Remote Interpreter`
 * In the dialogue box, select your docker interpreter
 * Click `OK`
 * Select the Path Mappings folder icon
 * Add the local project folder as `/app`
 * Click `OK`
 * `Path mappings` should now show `<Project root>->/app`
 * Under `PHPUnit library` set `Path to script:` under `Composer autoloader` to `/app/vendor/autoload.php`
 * Click the refresh icon, PHPUnit 8.X should be found
 * Click `OK`
 * This should close the Preferences dialogue

 * Go to `Run` -> `Edit Configurations` from the main menu
 * Click `+` and select `PHPUnit`
 * Set the test scope to either Directory or Defined in the config file
 * To set the alternative config file, be sure to tick the box
 * Click `OK`
 * Try to run the test suite
 * It may be necessary to set the `APP_ENV=test` environment variable again in the PHPUnit configuration;
   do that under `Run` -> `Edit Configurations` 

While these steps may work, there can be issues with configuring testing within PhpStorm. If there are
issues; stop where you are and move over to the terminal. Ensure all containers are running in the test
env and then use the `dc-phpunit` script first before attempting to configure PhpStorm again.

Sometimes it helps to remove all configuration starting from the Run Configurations all the way back to
Docker and then start again.

__Note:__ with compiled containers, changes to tests will not show up if SyncIt is not running. Ensure
SyncIt is transferring files to your docker container. If there are issues, perform a `docker-compose down`
and then `docker-compose up -d --build --force-recreate` to forcibly re-build the containers.

See the following resources for further help:

 * [PHPStorm Test Frameworks](https://www.jetbrains.com/help/phpstorm/2019.2/php-test-frameworks.html#PHP_test_frameworks_PHPUnit)
 * [PHPStorm Run Debug Config](https://www.jetbrains.com/help/phpstorm/2019.2/run-debug-configuration-phpunit.html)
