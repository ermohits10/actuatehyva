<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Test\Unit\Utils;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

class ValidatorHelperTest extends TestCase
{
    /**
     * @var \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper
     */
    protected $model;

    /**
     * @var DeliveryDateDataInterface|MockObject
     */
    protected $deliveryDateDataObjectMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneMock;

    /**
     * @return void
     */
    public function setUp(): void
    {

        $this->deliveryDateDataObjectMock = $this->getMockBuilder(DeliveryDateDataInterface::class)
                                                 ->disableOriginalConstructor()
                                                 ->getMock();

        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $args = [
            'timezone' => $this->timezoneMock
        ];

        /** @var \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper $model */
        $this->model = new \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper(...$args);
    }

    /**
     * Now it must throw exception. Need update with custom date format support in all inputs on the front.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testDayAsStringToDateTimeConversionInMDYFormat(): void
    {
        $selectedYear        = '2023';
        $selectedMonth       = '03';
        $selectedDay         = '25';
        $deliveryDaySelected = sprintf('%s/%s/%s', ...[$selectedMonth, $selectedDay, $selectedYear]);

        $timezone = 'America/Chicago';

        $this->deliveryDateDataObjectMock->expects($this->atLeastOnce())
                                         ->method('getDeliveryDay')
                                         ->willReturn($deliveryDaySelected);

        $this->timezoneMock->expects($this->atLeastOnce())
                           ->method('getConfigTimezone')
                           ->willReturn($timezone);

        $this->expectException(LocalizedException::class);
        $result = $this->model->getSelectedDay($this->deliveryDateDataObjectMock);
    }

    /**
     * Now it must throw exception. Need update with custom date format support in all inputs on the front.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testDayAsStringToDateTimeConversionInYMDFormat(): void
    {
        $selectedYear        = '2023';
        $selectedMonth       = '03';
        $selectedDay         = '25';
        $deliveryDaySelected = sprintf('%s-%s-%s', ...[$selectedYear, $selectedMonth, $selectedDay]);

        $timezone = 'America/Chicago';

        $this->deliveryDateDataObjectMock->expects($this->atLeastOnce())
                                         ->method('getDeliveryDay')
                                         ->willReturn($deliveryDaySelected);

        $this->timezoneMock->expects($this->atLeastOnce())
                           ->method('getConfigTimezone')
                           ->willReturn($timezone);

        $result = $this->model->getSelectedDay($this->deliveryDateDataObjectMock);
        $this->assertIsObject($result);
        $this->assertEquals($selectedYear, $result->format('Y'));
        $this->assertEquals($selectedMonth, $result->format('m'));
        $this->assertEquals($selectedDay, $result->format('d'));
    }
}
