# IntegerNet_SansecWatch Module

This module allows the integration CSP rules from Sansec Watch (https://sansec.watch/) into Magento without the need for file manipulations and a re-deployment

## Setup

```shell
composer require integer-net/magento2-sansec-watch
bin/magento module:enable IntegerNet_SansecWatch
bin/magento setup:upgrade
```

## Configuration

The configuration can be found under `Stores > Configuration > IntegerNet > Sansec Watch`.
Enable Sansec Watch and enter its project ID. You can find the ID in the project URL after navigating to https://sansec.watch/d/account/list and selecting a project (for example, `685769a2-38a4-4d06-a19a-67a528197f51`).

Enablement and the project ID can be configured at the Default or Store View scope. A store view inherits the Default configuration unless it has its own values. These settings are not configurable at Website scope. Policies and update status are saved per store view; if a store view has no policies yet, it uses the Default policy set.

Scheduled policy updates are disabled by default. To enable them, set `Enable cron job` at Default scope. This global setting controls the cron job that updates policies for all enabled store views.

## How it works

The policies are fetched from the Sansec Watch API and saved into a database table (`integernet_sansecwatch_policies`)
When Magento collects the CSP rules, it uses the `Magento\Csp\Model\CompositePolicyCollector` class and this module adds
a collector to this class, which will read the policies from the database table and add them to the existing policies.

Once policies are fetched from Sansec Watch, the result will be hashed and further updates are only executed, if the
newly fetched policies differ from the existing ones. (This is handled via the entry `integernet_sansecwatch` in the `flag` table)

### Full Page Cache (FPC)

After policies are updated (either by an updated list from sansec watch or by force), the FPC will be, depending on the configuration either cleared, invalidated or ignored.

## Usage

### Backend

Directly below the configuration is a button, `Update Policies Now`, which will fetch and update the policies on demand.
This will do a forced update, where rules are updated, even if the hashes of the old and new policies match.

### Command Line

An update can be triggered via `bin/magento integer-net:sansec-watch:update`
This updates policies for every enabled store view and by default only saves changes if the hashes of the old and new policies differ.

To update only one store view, pass its ID or code with `--store-id`:

```shell
bin/magento integer-net:sansec-watch:update --store-id=default
```

A dry-run is possible by adding the `--dry-run` flag, which fetches and outputs the policies without updating the database.

If an update should be force (regardless of the hashes), the `--force` flag can be added.

### Cronjob

The policies are also fetched via the cron job `integernet_sansecwatch_update`, which runs daily at 01:05 (cron expression: `5 1 * * *`) when `Enable cron job` is enabled at Default scope.
This will also only update the database, if the hashes of the old and new policies do not match.
