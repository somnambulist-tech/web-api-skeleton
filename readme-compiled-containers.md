# Compiled Containers

A compiled container is a Docker container that does **not** use mounted local folders i.e.: there
are no mappings in the docker-compose file to expose the application files to the container. This
is the preferred way to run the containers as the performance is much higher and on Docker for Mac
there are far fewer issues with Docker resources timing out.

The downside to this approach is that all the files are loaded into the container and are never
updated until the container is rebuilt. This is not desirable while developing as developers need
to be able to see code changes either real-time, or almost real-time.

There are several products to help keep a local file system in-sync with a running Docker
container namely: [Docker Sync](http://docker-sync.io) and [Mutagen](https://mutagen.io). The
preference is to use Mutagen as this will work with remote docker hosts where Docker Sync will not.

Mutagen is an application that can synchronize files from a source to a target. This can be done
by several mechanisms, but includes direct docker support (as well as FTP/SSH etc). The project
is still in development but works quite well and is pretty quick. Read more about it on the Mutagen
site linked previously.

To install Mutagen, use brew: `brew install mutagen-io/mutagen/mutagen`.

To help work with Mutagen a helper library is available: [SyncIt for Mutagen](https://github.com/somnambulist-tech/sync-it).
To install this library, follow the instructions and optionally use the lazy install. Note that
lazy install is performed at your own risk. 

SyncIt is a PHP Phar archive that wraps some of the mutagen functionality in an easier wrapper
that uses a YAML file format to configure sync tasks. Mutagen 0.10.0 has experimental support for
a similar setup however this is highly experimental and currently not used.

## Setup Mutagen Client

The first thing to do is create a default Mutagen global configuration. This is to prevent
accidentally deleting files by using a two-way-sync. A good set of defaults would be:

```yaml
sync:
    defaults:
        mode: one-way-replica
        ignore:
            vcs: true
            paths:
                # System files
                - ".DS_Store"
                - "._*"

                # Vim files
                - "*~"
                - "*.sw[a-p]"

                # Common folders and files
                - ".idea"
                - "node_modules"
                - "var/*"
                - "docker-compose*.yml"
        symlink:
            mode: ignore
        permissions:
            defaultFileMode: 0644
            defaultDirectoryMode: 0755
``` 

This should be stored in `~/.mutagen.yml` (your users home folder).

## Using SyncIt on a Project

A default configuration file is included in the skeleton project and provides sync tasks for:

 * src
 * vendor
 * composer.json/lock
 * migrations

The SyncIt file will use ENV vars defined in the project `.env` as well as any in the current
shell scope. You can check all available ENV vars by running: `syncit params`. Note this
requires that the config file be valid. To substitute an ENV var use Bash expansion syntax:
`${VAR_NAME}`.

To start the SyncIt tasks: `syncit start` and then choose which ones you want to start.

To stop the SyncIt tasks: `syncit stop` and again choose what to stop.

You can get extended information by running `syncit view` and get the details of a sync task.

Additionally: all commands can be debugged by adding `-vvv`. This will output the underlying
calls out to mutagen for debugging. For example:

```
$ syncit start -vvv
Would you like to start the daemon? (y/n) y
Which task would you like to start? 
  [0] app_source_files
  [1] app_vendor_files
  [2] composer_json
  [3] composer_lock
  [4] All
 > 0
Starting 1 sync tasks
  RUN  'mutagen' 'create' '/Users/anon/Projects/app-service' ... <lots more options>
Created session <hash>                            
  OUT  
  RES  Command ran successfully
 RUN  started session for "app_source_files" successfully
```

You can get the current task status by running: `syncit status`

```
$ syncit status
+------------------+----- Sync-It -- Active Tasks -- Mutagen (v0.10.0) -------+----------------------+
| Label            | Identifier                           | Conn State        | Sync Status          |
+------------------+--------------------------------------+-------------------+----------------------+
| app_source_files | <hash>                               | Connected         | Watching for changes |
| app_vendor_files | --                                   | --                | stopped              |
| composer_json    | --                                   | --                | stopped              |
| composer_lock    | --                                   | --                | stopped              |
+------------------+--------------------------------------+-------------------+----------------------+
| Run: "mutagen list" for raw output; or view <label> for more details                               |
+------------------+--------------------------------------+-------------------+----------------------+
```

__Important!__ Once you are done working on a project be sure to stop ALL `syncit` tasks to avoid issues.
