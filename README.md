# IntegerNet_SansecWatch Module

This module allows the integration CSP rules from Sansec Watch (https://sansec.watch/) into Magento without the need for file manipulations and a re-deployment

## Setup

```shell
# If the project is not already using packages.integer-net.de:
bin/composer config repositories.integer-net composer https://packages.integer-net.de/

bin/composer require integer-net/magento2-sansec-watch
bin/magento module:enable IntegerNet_SansecWatch
bin/magento setup:upgrade
```

## Configuration

The configuration can be found under `Stores > Configuration > IntegerNet > Sansec Watch`
Only the sansec watch project ID is needed, which can be found in the URL, if you navigate to https://sansec.watch/d/account/list and select a project
(e.g. `685769a2-38a4-4d06-a19a-67a528197f51`)

## How it works

The policies are fetched from the Sansec Watch API and saved into a database table (`integernet_sansecwatch_policies`)
When Magento collects the CSP rules, it uses the `Magento\Csp\Model\CompositePolicyCollector` class and this module adds
a collector to this class, which will read the policies from the database table and add them to the existing policies.

Once policies are fetched from Sansec Watch, the result will be hashed and further updates are only executed, if the
newly fetched policies differ from the existing ones. (This is handled via the entry `integernet_sansecwatch` in the `flag` table)

## Usage

### Backend

Directly below the configuration is a button, `Update Policies Now`, which will fetch and update the policies on demand.
This will do a forced update, where rules are updated, even if the hashes of the old and new policies match.

### Command Line

An update can be triggered via `bin/magento integer-net:sansec-watch:update`
This will by default only update the policies if the hashes of the old and new policies doesn't match.

A dry-run is possible by adding the `--dry-run` flag, which will only fetch and output the policies, but not update the 
database table.

If an update should be force (regardless of the hashes), the `--force` flag can be added.

### Cronjob

The policies are also fetched via the cronjob `integernet_sansecwatch_update`, which will run every hour (cron expression: `0 * * * *`)
This will also only update the database, if the hashes of the old and new policies do not match 
