# Cloud Deploy
Cloud Deploy is a cloud deployment system allowing nodes to poll a server for releases and deploy upgrades via pull.

This library is written in the [Silex PHP micro-framework](http://silex.sensiolabs.org/). Currently it only supports Git to perform file upgrades, and MySQL to store current releases.

How it works
------------

To fully leverage the highly available, elastic architecture of the cloud, you need to run a cluster of nodes behind a load balancer. Ideally, the cluster would grow and shrink with demand (eg AWS auto-scaling), meaning nodes are constantly being started and stopped.

Moving to this architechure leaves two problems for deployment:
* it is not known what nodes are currently alive without polling the load balancer or an API (ie AWS).
* for optimal security, the nodes should be firewalled from the outside world, with all inbound requests coming from the load balancer

By running Cloud Deploy on each server via a cron job (or other scheduling system), deployment upgrades are as simple as adding a release record to the database and waiting for each server to upgrade.

Requirements
------------

- php >= 5.4.0 (required extensions: pdo_mysql, php_intl)
- mysql >= 5.1
- git: >= 1.8
- *nix system
- mysql client >= 5.1

Installation
------------

To install Cloud Deploy simply unzip the package into a folder on your servers.

Copy *app/config/config.dist.yml* to *app/config/config.yml* and add the settings for your deployment and MySQL server:

``` yaml
deployments:
    <name of deployment 1>:
        type: git
        path: </path/to/deployment 1>
    <name of deployment 2>
        type: git
        path: </path/to/deployment 2>
        
database:
    driver: pdo_mysql
    host: <database host>
    user: <database user>
    password: <database password>
    dbname: <database name>
    port: <database port>
```

Then run

``` bash
$ curl -s https://getcomposer.org/installer | php
$ composer install
```

And finally to install the database

``` bash
$ php app/console cloud-deploy:install
```

*Note: the configured database user must have permission to execute `CREATE DATABASE` and `CREATE TABLE` commands. If not, you can manually create the database and run the installation SQL file (`src/CloudDeploy/Resources/sql/install.sql`)*

How to upgrade nodes
----------

To read the current release from the database and perform an upgrade, run:

``` bash
$ /usr/bin/php </path/to/cloud-deploy>/app/console do-upgrade <name of deployment 1>
```

Ideally, schedule this as a cron job:

``` bash
# crontab file

# m h  dom mon dow   command

# Check for releases every 5 minutes
*/5 0 0 0 0 /usr/bin/php </path/to/cloud-deploy>/app/console do-upgrade <name of deployment 1>
*/5 0 0 0 0 /usr/bin/php </path/to/cloud-deploy>/app/console do-upgrade <name of deployment 2>

```

How to create a release
----------

Releases are kept in the `releases` table in the database. Simply insert a new record into this table for the nodes to perform an upgrade.

``` sql
INSERT INTO `releases` (`deployment`, `version`, `release_date`)
VALUES ('<name of deployment 1>', 'tag:1.5.2', NOW());
```

Accepted values for version are:
- `branch:<name of branch>`
- `tag:<name of tag>`
- `commit:<commit sha>`

*Note: Don't put spaces around the colon (':')*

How to monitor upgrades
----------

Each node creates an upgrade record in the `upgrades` table, using its hostname for reference. During the course of an upgrade, the current state is recorded in the `upgrade_status` field.
