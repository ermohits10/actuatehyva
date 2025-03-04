<?php

namespace Craftyclicks\Ukpostcodelookup\Model\Source;

class Searchtype implements \Magento\Framework\Data\OptionSourceInterface
{
	/**
	* @return array
	*/
	public function toOptionArray()
	{
		return [
			['value' => 'searchbar_text', 'label' => __('SearchBar')],
			['value' => 'traditional', 'label' => __('Traditional')]
		];
	}
}
