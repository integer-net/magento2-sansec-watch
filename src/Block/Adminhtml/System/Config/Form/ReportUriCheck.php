<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\Exception\InvalidConfigurationException;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Override;
use Symfony\Component\Uid\Uuid;

use function is_string;
use function str_contains;

class ReportUriCheck extends Field
{
    protected $_template = 'IntegerNet_SansecWatch::system/config/form/report-uri-check.phtml';

    private ?string $htmlId = null;

    private array $configurationStatusMap = [];
    private ?Uuid $sansecWatchId = null;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private readonly Config $config,
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

    public function getHtmlId(): string
    {
        return $this->htmlId ?? '';
    }

    public function getConfigurationPaths(): array
    {
        // For now, we only check the generic storefront report uri
        return [
            'csp/mode/storefront/report_uri',
        ];
    }

    public function getConfigurationStatus(string $path): bool
    {
        if (isset($this->configurationStatusMap[$path])) {
            return $this->configurationStatusMap[$path];
        }

        $configuredReportUri = $this->getReportUri($path);
        if (!$configuredReportUri) {
            return false;
        }

        return str_contains(
            $configuredReportUri,
            $this->getConfiguredSansecId() . '.sansec.watch',
        );
    }

    public function getReportUri(string $configPath): ?string
    {
        $configuredReportUri = $this->_scopeConfig->getValue($configPath);

        return is_string($configuredReportUri)
            ? $configuredReportUri
            : null;
    }

    public function canShowMessage(): bool
    {
        if (!$this->getConfiguredSansecId() instanceof Uuid) {
            return false;
        }

        foreach ($this->getConfigurationPaths() as $path) {
            if (!$this->getConfigurationStatus($path)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    private function getConfiguredSansecId(): ?Uuid
    {
        if (!$this->sansecWatchId instanceof Uuid) {
            try {
                $this->sansecWatchId = $this->config->getId();
            } catch (InvalidConfigurationException) {
                $this->sansecWatchId = null;
            }
        }

        return $this->sansecWatchId;
    }
}
