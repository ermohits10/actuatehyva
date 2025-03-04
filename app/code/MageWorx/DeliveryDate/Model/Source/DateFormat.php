<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DateFormat implements OptionSourceInterface
{
    const DEFAULT_DATE_FORMAT = 'yyyy-MM-dd';
    const CUSTOM_FORMAT_VALUE = '';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * @var array
     */
    private $formats;

    /**
     * DateFormat constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param array $formats
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        $formats = []
    ) {
        $this->timezone = $timezone;
        $this->resolver = $resolver;
        $this->formats  = $formats;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        $options         = [];
        $dateForExamples = \DateTime::createFromFormat('d-m-Y', '19-01-1989');
        if (!$isMultiSelect) {
            $options[0] = [
                'label' => __('Default (%1)', $this->convertDate($dateForExamples, static::DEFAULT_DATE_FORMAT)),
                'value' => static::DEFAULT_DATE_FORMAT
            ];
        }

        foreach ($this->formats as $format) {
            $options[] = [
                'label' => $this->convertDate($dateForExamples, $format),
                'value' => $format
            ];
        }

        $options[] = [
            'label' => __('Custom Format'),
            'value' => static::CUSTOM_FORMAT_VALUE
        ];

        return $options;
    }

    /**
     * @param \DateTimeInterface $date
     * @param string $format
     * @return string
     */
    public function convertDate(\DateTimeInterface $date, string $format): string
    {
        $dateFormatted = $this->timezone->formatDateTime(
            $date,
            null,
            null,
            $this->resolver->getLocale(),
            null,
            $format
        );

        return $dateFormatted;
    }
}
