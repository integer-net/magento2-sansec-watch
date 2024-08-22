<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use DateTimeImmutable;
use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Dates extends Field
{
    protected $_template = 'IntegerNet_SansecWatch::system/config/form/dates.phtml';

    private ?string $htmlId = null;
    private ?SansecWatchFlag $sansecWatchFlag = null;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private readonly FlagManager $flagManager,
        private readonly SansecWatchFlagMapper $flagDataMapper,
        private readonly TimezoneInterface $timezone,
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
    }

    public function render(AbstractElement $element): string
    {
        $this->htmlId = $element->getHtmlId();

        return parent::render($element);
    }

    public function getHtmlId(): string
    {
        return $this->htmlId ?? '';
    }

    public function getLastCheckedAtDate(): string
    {
        $lastCheckedAt = $this->getFlagData()?->lastCheckedAt;

        return $lastCheckedAt instanceof DateTimeImmutable
            ? $this->formatDateTime($lastCheckedAt)
            : '';
    }

    public function getLastUpdatedAtDate(): string
    {
        $lastUpdatedAt = $this->getFlagData()?->lastUpdatedAt;

        return $lastUpdatedAt instanceof DateTimeImmutable
            ? $this->formatDateTime($lastUpdatedAt)
            : '';
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $this->timezone->formatDateTime($dateTime, timeType: IntlDateFormatter::MEDIUM);
    }

    private function getFlagData(): ?SansecWatchFlag
    {
        if (!$this->sansecWatchFlag instanceof SansecWatchFlag) {
            /** @var null|array{hash: string, lastCheckedAt: string, lastUpdatedAt: string} $flagData */
            $flagData = $this->flagManager->getFlagData(SansecWatchFlag::CODE);

            if ($flagData === null) {
                return null;
            }

            $this->sansecWatchFlag = $this->flagDataMapper->map($flagData);
        }

        return $this->sansecWatchFlag;
    }
}
