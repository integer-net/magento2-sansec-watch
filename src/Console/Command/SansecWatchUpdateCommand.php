<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Console\Command;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Exception\CouldNotFetchPoliciesException;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use IntegerNet\SansecWatch\Model\Exception\InvalidConfigurationException;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;
use function __;

class SansecWatchUpdateCommand extends Command
{
    private const string OPTION_DRY_RUN  = 'dry-run';
    private const string OPTION_FORCE    = 'force';
    private const string OPTION_STORE_ID = 'store-id';

    public function __construct(
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
        private readonly StoreManagerInterface $storeManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('integer-net:sansec-watch:update')
            ->setDescription('Update the CSP whitelist from sansec watch')
            ->addOption(
                name: self::OPTION_DRY_RUN,
                mode: InputOption::VALUE_NONE,
            )
            ->addOption(
                name: self::OPTION_FORCE,
                mode: InputOption::VALUE_NONE,
            )
            ->addOption(
                name: self::OPTION_STORE_ID,
                mode: InputOption::VALUE_REQUIRED,
                description: (string) __('Store view ID or code. Omit to update all enabled store views.'),
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isDryRun = (bool) $input->getOption(self::OPTION_DRY_RUN);
        $isForce = (bool) $input->getOption(self::OPTION_FORCE);
        $storeIdentifier = $input->getOption(self::OPTION_STORE_ID);

        $io = new SymfonyStyle($input, $output);

        try {
            $stores = $storeIdentifier === null
                ? $this->storeManager->getStores()
                : [$this->storeManager->getStore($storeIdentifier)];
        } catch (LocalizedException $e) {
            $io->error((string) __('Could not find store with ID or code %1', $storeIdentifier));
            $io->warning($e->getMessage());

            return self::INVALID;
        }

        $hasErrors = false;
        foreach ($stores as $store) {
            $storeId = (int) $store->getId();
            $io->info((string) __('Store: %1 (%2)', $store->getName(), $store->getCode()));

            if (!(bool) $store->getIsActive()) {
                $io->warning((string) __('Store view is inactive'));
                continue;
            }

            if (!$this->config->isEnabled($storeId)) {
                $io->warning((string) __('Update is disabled for this store view'));
                continue;
            }

            try {
                $uuid = $this->config->getId($storeId);
                $policies = $this->sansecWatchClientFactory
                    ->create()
                    ->fetchPolicies($uuid);
            } catch (InvalidConfigurationException|CouldNotFetchPoliciesException $exception) {
                $io->error($exception->getMessage());
                $hasErrors = true;
                continue;
            }

            if ($isDryRun || $output->isVerbose()) {
                $io->info((string) __('Fetched policies:'));

                $io->table(
                    ['Directive', 'Host'],
                    array_map(static fn(Policy $policy): array => $policy->toArray(), $policies),
                );
            }

            if ($isDryRun) {
                continue;
            }

            try {
                $this->policyUpdater->updatePolicies($policies, $isForce, $storeId);
            } catch (CouldNotUpdatePoliciesException $exception) {
                $io->error($exception->getMessage());
                $hasErrors = true;
            }
        }

        return $hasErrors
            ? Cli::RETURN_FAILURE
            : Cli::RETURN_SUCCESS;
    }
}
