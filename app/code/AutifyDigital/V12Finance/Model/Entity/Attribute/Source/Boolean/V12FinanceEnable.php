<?php

namespace AutifyDigital\V12Finance\Model\Entity\Attribute\Source\Boolean;

class V12FinanceEnable extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        }
        return $this->_options;
    }
    
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => __('No')),
            array('value' => '1', 'label' => __('Yes'))
        );
   }
}
