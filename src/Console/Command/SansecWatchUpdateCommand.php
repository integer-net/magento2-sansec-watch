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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SansecWatchUpdateCommand extends Command
{
    private const OPTION_DRY_RUN = 'dry-run';

    private const OPTION_FORCE = 'force';

    public function __construct(
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('integer-net:sansec-watch:update')
            ->setDescription('Update the CSP whitelist from sansec watch')
            ->addOption(
                name: self::OPTION_DRY_RUN,
                shortcut: null,
                mode: InputOption::VALUE_NONE,
            )
            ->addOption(
                name: self::OPTION_FORCE,
                shortcut: null,
                mode: InputOption::VALUE_NONE,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isDryRun = (bool)$input->getOption(self::OPTION_DRY_RUN);
        $isForce = (bool)$input->getOption(self::OPTION_FORCE);

        $io = new SymfonyStyle($input, $output);

        if (!$this->config->isEnabled()) {
            $io->warning('Update is disabled');
            return self::SUCCESS;
        }

        try {
            $uuid = $this->config->getId();
        } catch (InvalidConfigurationException $invalidConfigurationException) {
            $io->error($invalidConfigurationException->getMessage());
            return self::FAILURE;
        }

        try {
            $policies = $this->sansecWatchClientFactory
                ->create()
                ->fetchPolicies($uuid);
        } catch (CouldNotFetchPoliciesException $couldNotFetchPoliciesException) {
            $io->error($couldNotFetchPoliciesException->getMessage());
            return self::FAILURE;
        }

        if ($isDryRun || $output->isVerbose()) {
            $io->info('Fetched policies:');

            $io->table(
                ['Directive', 'Host'],
                array_map(fn (Policy $policy): array => $policy->toArray(), $policies)
            );
        }

        try {
            $this->policyUpdater->updatePolicies($policies, $isForce);
        } catch (CouldNotUpdatePoliciesException $couldNotUpdatePoliciesException) {
            $io->error($couldNotUpdatePoliciesException->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
