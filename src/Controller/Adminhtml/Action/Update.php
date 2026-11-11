<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Controller\Adminhtml\Action;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Model\StoreScopeResolver;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Override;

use function __;

class Update extends Action
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
        private readonly StoreScopeResolver $storeScopeResolver,
        Context $context,
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        try {
            $storeId = $this->storeScopeResolver->resolveStoreId($this->request->getParam('store'));
        } catch (LocalizedException $localizedException) {
            return $result->setData([
                'success' => false,
                'message' => __('Could not update policies: %1', $localizedException->getMessage()),
            ]);
        }

        if (!$this->config->isEnabled($storeId)) {
            return $result->setData([
                'success' => true,
                'message' => __('Please enable updates first.'),
            ]);
        }

        try {
            $uuid = $this->config->getId($storeId);

            $policies = $this->sansecWatchClientFactory
                ->create()
                ->fetchPolicies($uuid);

            $this->policyUpdater->updatePolicies($policies, storeId: $storeId);
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

    #[Override]
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('IntegerNet_SansecWatch::configuration');
    }
}
