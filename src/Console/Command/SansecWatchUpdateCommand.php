<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Console\Command;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SansecWatchUpdateCommand extends Command
{
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
            ->setDescription('Update the CSP whitelist from sansec watch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uuid = $this->config->getUuid();

        $policies = $this->sansecWatchClientFactory
            ->create()
            ->fetchPolicies($uuid);

        // TODO: error handling

        $this->policyUpdater->updatePolicies($policies);

        $io->success('Policies updated');

        return self::SUCCESS;
    }
}
