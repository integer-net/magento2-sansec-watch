<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use Exception;
use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\StoreScopeResolver;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Override;

use function sprintf;
use function __;

class Buttons extends Field
{
    private const string SANSEC_WATCH_DASHBOARD_URL = 'https://sansec.watch/d/%s';

    protected $_template = 'IntegerNet_SansecWatch::system/config/form/buttons.phtml';

    private ?string $htmlId = null;

    public function __construct(
        private readonly RequestInterface $request,
        private readonly Config $config,
        private readonly StoreScopeResolver $storeScopeResolver,
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
    ) {
        parent::__construct($context, $data, $secureRenderer);
    }

    #[Override]
    public function render(AbstractElement $element): string
    {
        $this->htmlId = $element->getHtmlId();

        return parent::render($element);
    }

    public function getAjaxUrl(): string
    {
        $store = $this->request->getParam('store');
        $parameters = $store !== null
            ? ['store' => $store]
            : [];

        return $this->getUrl('integernet_sansecwatch/action/update', $parameters);
    }

    public function getHtmlId(): string
    {
        return $this->htmlId ?? '';
    }

    public function getUpdatePoliciesButtonHtml(): string
    {
        try {
            $buttonData = [
                'id' => $this->getHtmlId() . '_update_policies_button',
                'label' => __('Update Policies Now'),
            ];

            return $this->createButton()
                ->setData($buttonData)
                ->toHtml();
        } catch (Exception) {
            return '';
        }
    }

    public function canShowVisitDashboardAction(): bool
    {
        try {
            $this->config->getId($this->getStoreId());
        } catch (LocalizedException) {
            return false;
        }

        return true;
    }

    public function getVisitDashboardUrl(): string
    {
        try {
            return sprintf(
                self::SANSEC_WATCH_DASHBOARD_URL,
                $this->config->getId($this->getStoreId()),
            );
        } catch (LocalizedException) {
            return '';
        }
    }

    #[Override]
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @throws LocalizedException
     */
    private function createButton(): Button
    {
        /** @var Button $block */
        $block = $this->getLayout()
            ->createBlock(Button::class);

        return $block;
    }

    private function getStoreId(): ?int
    {
        return $this->storeScopeResolver->resolveStoreId($this->request->getParam('store'));
    }
}
