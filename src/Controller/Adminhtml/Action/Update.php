<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Controller\Adminhtml\Action;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Update extends Action
{
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
        Context $context,
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->config->isEnabled()) {
            return $result->setData([
                'success' => true,
                'message' => __('Please enable updates first.'),
            ]);
        }

        try {
            $uuid = $this->config->getId();

            $policies = $this->sansecWatchClientFactory
                ->create()
                ->fetchPolicies($uuid);

            $this->policyUpdater->updatePolicies($policies);
        } catch (LocalizedException $localizedException) {
            return $result->setData([
                'success' => false,
                'message' => __('Could not update policies: %1', $localizedException->getMessage()),
            ]);
        }

        return $result->setData([
            'success' => true,
        ]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('IntegerNet_SansecWatch::configuration');
    }
}
