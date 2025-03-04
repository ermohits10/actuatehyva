<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source\FrontendModel;

class CustomDateFormat extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $afterElementHtml = $element->getData('after_element_html');
        $afterElementHtml .= $this->getFormatsTableHtml();
        $element->setData('after_element_html', $afterElementHtml);

        return parent::_getElementHtml($element);
    }

    /**
     * @see http://php.net/manual/en/function.date.php
     * @return string
     */
    private function getFormatsTableHtml()
    {
        $tableRaw  = <<<RAWDATA
        d 	Day of the month without leading zeros 	1 to 31
        dd	Day of the month 2 digits with leading zeros 	01 to 31
        E 	A textual representation of a day, three letters 	Mon through Sun
        EEEE 	A full textual representation of the day of the week 	Sunday through Saturday
        e 	Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
        LLLL 	A full textual representation of a month, such as January or March 	January through December
        LL 	Numeric representation of a month, with leading zeros 	01 through 12
        LLL 	A short textual representation of a month, three letters 	Jan through Dec
        L 	Numeric representation of a month, without leading zeros 	1 through 12
        y 	A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
        yy 	A two digit representation of a year 	Examples: 99 or 03
RAWDATA;
        $tableRows = explode("\n", $tableRaw);
        $formats   = [];
        foreach ($tableRows as $row) {
            $tableCells = explode("\t", $row);
            $formats[]  = $tableCells;
        }
        $formatLabel      = __('Format');
        $descriptionLabel = __('Description');
        $exampleLabel     = __('Example');

        $tBodyContent = '';

        foreach ($formats as $tr) {
            $trFormat      = $tr[0] ?? '';
            $trDescription = $tr[1] ?? '';
            $trExample     = $tr[2] ?? '';

            $tBodyContent .= <<<CONTENT
        <tr>
            <td>$trFormat</td>
            <td>$trDescription</td>
            <td>$trExample</td>
        </tr>
CONTENT;
        }

        $moreInfoLink = __('More info');
        return <<<TABLE
<table class="admin__table-primary">
    <thead>
        <tr>
            <td>$formatLabel</td>
            <td>$descriptionLabel</td>
            <td>$exampleLabel</td>
        </tr>
    </thead>
    <tbody>
        $tBodyContent
        <tr>
            <td colspan="2">
            </td>
            <td>
                <a href="http://userguide.icu-project.org/formatparse/datetime" target="_blank">$moreInfoLink</a>
            </td>
        </tr>
    </tbody>
</table>
TABLE;
    }
}
