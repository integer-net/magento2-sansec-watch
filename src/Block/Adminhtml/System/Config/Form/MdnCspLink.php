<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Override;

class MdnCspLink extends Field
{
    protected $_template = 'IntegerNet_SansecWatch::system/config/form/mdn-csp-link.phtml';

    private ?string $htmlId = null;

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

    #[Override]
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }
}
