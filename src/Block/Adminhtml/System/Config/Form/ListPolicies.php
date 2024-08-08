<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ListPolicies extends Field
{
    protected $_template = 'IntegerNet_SansecWatch::system/config/button/list-policies.phtml';

    private ?string $htmlId = null;

    /**
     * @phpstan-param array<array-key, mixed> $data
     */
    public function __construct(
        private readonly GetAllPolicies $getAllPolicies,
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

    /**
     * @return list<Policy>
     */
    public function getPolicies(): array
    {
        return $this->getAllPolicies->execute();
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }
}
