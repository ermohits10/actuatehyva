<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use IntlDateFormatter;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use ResourceBundle;

class DeliveryDateInOrderGrid extends Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var DataBundle
     */
    private $dataBundle;

    /**
     * DeliveryDateInOrderGrid constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param array $components
     * @param array $data
     * @param ResolverInterface|null $localeResolver
     * @param DataBundle|null $dataBundle
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        ResolverInterface $localeResolver,
        DataBundle $dataBundle,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->booleanUtils = $booleanUtils;
        $this->localeResolver = $localeResolver;
        $this->locale = $this->localeResolver->getLocale();
        $this->dataBundle = $dataBundle;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['filter'])) {
            $config['filter'] = [
                'filterType' => 'dateRange',
                'templates' => [
                    'date' => [
                        'options' => [
                            'dateFormat' => $config['dateFormat'] ?? $this->timezone->getDateFormatWithLongYear()
                        ]
                    ]
                ]
            ];
        }

        $localeData = $this->dataBundle->get($this->locale);
        /** @var ResourceBundle $monthsData */
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $months = array_values(iterator_to_array($monthsData['format']['wide']));
        $monthsShort = array_values(
            iterator_to_array(
                null !== $monthsData->get('format')->get('abbreviated')
                    ? $monthsData['format']['abbreviated']
                    : $monthsData['format']['wide']
            )
        );

        $config['storeLocale'] = $this->locale;
        $config['calendarConfig'] = [
            'months' => $months,
            'monthsShort' => $monthsShort,
        ];
        if (!isset($config['dateFormat'])) {
            $config['dateFormat'] = $this->timezone->getDateFormat(IntlDateFormatter::MEDIUM);
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                    && $item[$this->getData('name')]
                ) {
                    $rawDate = new \DateTime(
                        (string)$item[$this->getData('name')],
                        new \DateTimeZone($this->timezone->getConfigTimezone())
                    );
                    $date = $this->timezone->date($rawDate);
                    $timezone = isset($this->getConfiguration()['timezone'])
                        ? $this->booleanUtils->convert($this->getConfiguration()['timezone'])
                        : true;
                    if (!$timezone) {
                        $date = new \DateTime((string)$item[$this->getData('name')]);
                    }
                    $item[$this->getData('name')] = $date->format('Y-m-d');
                } else {
                    $item[$this->getData('name')] = __('Empty');
                }
            }
        }

        return $dataSource;
    }
}
