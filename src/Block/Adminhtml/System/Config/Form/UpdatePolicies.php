<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Block\Adminhtml\System\Config\Form;

use Exception;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class UpdatePolicies extends Field
{
    protected $_template = 'IntegerNet_SansecWatch::system/config/button/update-policies.phtml';

    public function render(AbstractElement $element): string
    {
        $element->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    public function getAjaxUrl(): string
    {
        return $this->getUrl('integernet_sansecwatch/action/update');
    }

    public function getButtonHtml(): string
    {
        try {
            $buttonData = [
                'id' => 'update_policies_button',
                'label' => __('Update Policies Now'),
            ];

            return $this->createButton()
                ->setData($buttonData)
                ->toHtml();
        } catch (Exception) {
            return '';
        }
    }

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
}
