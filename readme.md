# Symfony Micro Service Starter Project

This is a skeleton project that pre-configures a Symfony 5+ project for use as a micro service.
This project is intended to be used in conjunction with: [Data Service](https://github.com/somnambulist-tech/data-service-skeleton)

The setup includes:

 * doctrine
 * doctrine-fixtures
 * doctrine-migrations
 * messenger
 * profiler
 * command/query/domain event buses
 * test helpers
 * docker configuration for app and redis containers
 * docker app container is configured without local mounts
 * shell scripts in `bin/` that call libs in docker
 * PHP container uses php-pm as the application server
 * Mutagen via SyncIt with a default configuration
 
Assorted readme files are included for different parts of the service setup:

 * [Compiled Containers](readme-compiled-containers.md)
 * [Service Discovery](readme-service-discovery.md)
 * [Setting up Supervisor for Tasks](readme-supervisor-tasks.md)
 * [Testing](readme-testing.md)

__Note:__ the data services pieces have been moved to a separate project to keep the micro services
scope narrow. See [Data Service](https://github.com/somnambulist-tech/data-service-skeleton) for the basic
files.

## Getting Started

Create a new project using composer:

`composer create-project somnambulist/symfony-micro-service <folder> --no-scripts`

Customise the base files as you see fit; change names, (especially the service names), config values etc
to suite your needs. Then: `docker-compose up -d` to start the docker environment in dev mode.
Be sure to read [Service Discovery](readme-service-discovery.md) to understand some of how the docker
environment is setup.

__Note:__ to use the latest version add `dev-master` as the last argument when creating a project. This
will checkout and use the current master version, instead of a tagged release.

### Recommended First Steps

This project uses `App` and `example.dev` throughout. Your first step would be to change the base PHP
namespace (if desired). PhpStorms refactoring / renaming is highly recommended for this action.

The domain name is set in several places, it is strongly recommended to change this to something more
useful. The following files should be updated:

 * .env
 * docker-compose*.yml

You should be sure to read [Compiled Containers](readme-compiled-containers.md).

#### Configured Services

The following docker services are pre-configured for development:

 * Redis
 * PHP 7.3 running php-pm 2.X

Test config includes all services to successfully run tests.

Release / production only defines the app as it is intended to be deployed into a cluster.

#### Docker Service Names

The Docker container names will be prefixed by a project name defined in the `.env` file. This is
the constant `COMPOSE_PROJECT_NAME`. If you remove it, the current folder name will be used instead.
For example: you create a new project called "invoice-service", without setting the COMPOSE constant
the containers started via `docker-compose` will be prefixed with `invoice-service_`. If you have a
lot of docker projects, they may have similar folder names, so using this constant avoids collisions.

The second constant that needs setting is `APP_SERVICE_APP`. This is the name of the PHP application
container. By default this is `app`. It is strongly recommended to change this to something that is
more unique. If you do change this, be sure to change the container name in the `docker-compose*.yml`
files otherwise it will not be used. This name is used by SyncIt to resolve the application container
and by the `bin/dc-*` scripts.

#### DNS Resolution

DNS and Proxy where moved to [data service](https://github.com/somnambulist-tech/data-service-skeleton).

## Suggested Implementation Approach

### Notes

 * Twig is enabled for: `dev`, `test` and `docker` environments and disabled in `prod`
 * As of 2020-02-19 the form-request bundle does not have an SF 5 stable release
 * Docker ppm container requires the latest build with an SF 5 ppm version

### Domain

The domain represents the solution to a business problem. It includes all the code necessary to
implement and solve that problem; without relying too heavily on third party or framework code.
It should be (ideally) framework agnostic and be portable to other frameworks if they prove to be
a better fit with a minimum of modifications to the core domain classes. i.e. you do not couple to
a framework validator or service container and avoid injecting implementations but use interfaces
instead.

The domain is typically discovered during the project setup with discussions with the main
stakeholders and domain experts - the people who really know and understand how the business operates.
That information is then used to create the software solution. The most important aspect of this is
the language that is discovered that allows all people to effectively communicate and know what is
meant by specific terms. The language is _not_ set in stone and changes over time as knowledge is
gained or the processes are improved. It is important to keep these changes up-to-date and this
includes the code itself.

This project suggests and has the following folder layout for the domain:

 * Commands
 * Events
 * Models
 * Queries
 * Services
 
These are suggestions and you are free to change this up if you wish.

#### Models

This project is centred around a Domain Driven Design approach, with Doctrine providing persistence
for the main domain objects. These models are located in: `src/Domain/Models`. All domain models
should be located here, including enumerations, value objects, and other data centric models.
Unlike standard Symfony projects, models should not contain Doctrine mapping annotations. Add these
to the `config/mappings` folder in a separate folder (default is `models`).

Your models should focus on the domain "state" and how various actions should be applied to it. This
means enforcing valid state changes i.e.: you do not need getters and setters. In fact you should
avoid adding these as the role of the models is to manage the state and not provide an API to query
that state. Essentially your models represent the write operations. In many cases these will use
value-objects and enumerables to ensure valid data is passed to the domain at all times. When using
simple scalars, strict-types should be enabled and all scalar type hints used.

Within your domain models there will be some that are key and are accessed externally. These are
likely to be your aggregate roots. Each aggregate root should raise appropriate domain events 
after each critical state transition. A doctrine listener is pre-enabled to listen for and propagate
the domain events to the pre-configured RabbitMQ fan-out exchange. Examples of aggregate roots may
include User, Account, Order etc. however it will depend on your domain.

In general your domain models will follow the business concepts and use terminology that is familiar
to the business. For example: if creating a service for the sales team, and they work with "leads"
then your domain should have a "Lead" model and it should have whatever properties they consider to
be important. The sales team should be able to look at the code and at least grasp the names and
concepts that it expresses.

#### Services

Services should contain classes that interact with the domain or provide additional support to the
code domain models e.g.: transformations, or translations between data types / formats. Repositories
are part of the domain services. A key idea though: is that the domain services are not dependent on
framework code. They are standalone, and encapsulated - just like the models.

For example a currency converter could be a domain service; or an authenticator that checks if an
object is accessible by another object based on domain rules (not framework rules).

#### Repositories

Each aggregate root should have a Repository service defined for it. This should be an interface that
then receives a Persistence implementation. The interface should be kept as simple as possible, typically:

 * find(Uuid $id): Object
 * store(Object $object): bool
 * destroy(Object $object): bool
 
Where the interface is coded to that specific object. Under the hood this may use Doctrine ObjectManager
to persist and delete objects.

Note that it is not necessary to call `->flush()` as a command bus should be used that includes DB
transaction wrapping.

### Command Query Responsibility Segregation

#### Commands

A "command" is a request to make a change to the system; such as: "create a user" or "activate a thing".
Commands are dispatched via a CommandBus that does not return any output. A command should be fully
encapsulated with all the necessary data need to action that request. This includes any generated ids
before hand i.e.: using this system you should not be relying on database auto-increments or sequences
(in this case these are surrogate identities that are used to make database modelling easier). Instead
you should only expose UUIDs of the main objects and only if necessary expose internal ids or use an
aggregate ID generation strategy such as a counter that increments continually as records are added.

When the command is dispatched, the command bus handles it along with any errors that may occur. These
will be raised as an exception that the custom JSON Exception subscriber will collect and transform to
API error messages. This can be overridden by adding appropriate error handling.

The command bus uses the following middleware:

 * validation
 * doctrine_transaction
 
Additional middlewares can be configured in the `config/packages/messenger.yaml` file.

Commands may only be handled by one handler; but a handler may raise more commands to be dispatched
if deemed appropriate. However: even in this instance it would be better to write an event listener
for a domain event and respond to that as domain events are broadcast after all Doctrine operations
have been flushed to the data store.

The command handler may make whatever changes are necessary via calling into the domain models.
This includes creating new objects, loading existing ones, interacting with the repository or other
services.

Typically your commands will correspond to actual actions that the business carries out and should
be named as such.

#### Queries

A query is a request for information from the system. The query might be "Find me X by Id" or "find all
products matching these criteria...". A QueryBus then executes the query command and returns a result.
The query encapsulates all the data that has been requested and should never include the originating
request object. It is safe to use value objects and primitives. Several abstract query commands are
included for basic actions (provided by `somnambulist/domain`).

Query commands are immutable and should not be changed; the only concession is if using the includes
support to load sub-objects where a `with()` method is added.

The query command is handled by a QueryHandler that accepts that command as an argument to the magic
`__invoke` method. How the query is handled is entirely up to the implementor. It could be pure SQL,
API calls, DQL, parse some files, return hard coded responses etc etc.

For example a query command may look like:

```php
<?php

use Somnambulist\Domain\Queries\AbstractQuery;

class FindObjectById extends AbstractQuery
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
```

This would then be executed by a QueryHandler that would have the following signature:

```php
<?php

class FindObjectByIdQueryHandler
{
    
    public function __invoke(FindObjectById $query)
    {
        // do some operations to find the thing
        return $object;
    }
}
```

Using a QueryBus allows the query handling to be changed at any time by replacing the query handler
with another implementation. For example: we start off with a service that gets large and requires
splitting up, queries into the part that is split off do not need to change, only the handler needs
updating to make API calls instead and can still return the same objects as before. No changes would
be needed in the controllers.

The down side to this approach are many small files; however each of the files is completely testable
in isolation.

### Delivery

The Delivery folder is for any output mechanisms that will produce a response from the system. Here
is where any API or web controllers live, console commands, etc. ViewModels would live in this part
of the system.

Each major output type should be kept segregated in it's own namespace to avoid polluting e.g. the web
responses with API responses.

By default `Api` and `Console` are provided and are mapped as services already in the `services.yaml` file.

The API is intended to be fully versioned right from the get go - to ensure backwards compatibility.
This versioning should be done at the controller, form and transformer level. Each version should
have its own controllers, form requests and transformers. If a particular version does not change one
output, you could re-use a previous version if needs be.

FormRequests are a concept from Laravel where you can type hint a validate request object that will
ensure that the request contains the data defined in the rules. It provides a somewhat cleaner setup
to the Symfony Form library, that can be rather complex to deal with. Using this library is entirely
optional. See [Form Request Bundle](https://github.com/adamsafr/form-request-bundle) for more details.

For controllers it is best to group then around an aggregate root e.g. there is a User aggregate, so
there would be a `Users` folder in the `src/Delivery/Api/V1` folder. Within this folder you could
arrange it with folders for `Forms` and `Transformers` or include specific `ViewModels` too.

For the controllers it is best to follow a single controller per action approach e.g.: instead of one
controller that contains methods for create, update, destroy, view, list; these are instead separate
controllers: `CreateController`, `ListController`, `ViewController` etc. It is up to you how you name
these. They could instead by named: `DisplayUserAsJson` instead of `ViewController` etc. Whatever
naming strategy is used, it should be used consistently.

To help with handling some of the typical request/response cycle of a controller a helper library
(`somnambulist/api-bundle`) is included. This integrated Fractal response transformer through a
system similar to DingoAPI. When used in conjunction with the command and query buses, this allows
for very thin and light-weight controllers; keeping most of the business logic within the command
and query handlers.

#### View Models

For querying the system e.g. for an API response, create a ViewModel instead of using the main domain
models. This allows for customised represents to be used including presentation logic, without filling
the domain models with presentation logic. A package: `somnambulist/read-models` is included to provide
this functionality via an active-record approach, however pure SQL / PDO could be used instead.

See the read-models documentation for more details of working with the library.

## Contributing

Contributions are welcome! Spot an error, want additional docs or something better explaining? Then
create a ticket on the project, or open a PR.
