<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Test\Unit\DeliveryDateValidation;

use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\TestCase;

class ValidatorPoolTest extends TestCase
{
    /**
     * @var \MageWorx\DeliveryDate\Model\Validator\DeliveryDateValidatorPool
     */
    protected $model;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $deliveryDateDataMock;

    /**
     * @var \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $deliveryDateValidatorHelperMock;

    /**
     * @var \MageWorx\DeliveryDate\Api\DeliveryOptionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $deliveryOptionMock;

    /**
     * @var \MageWorx\DeliveryDate\Model\Validator\ValidatePassDate
     */
    protected $deliveryDateValidatorDateInPast;

    /**
     * @var \MageWorx\DeliveryDate\Model\Validator\ValidateDayLimits
     */
    protected $deliveryDateValidatorDayLimits;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->deliveryDateDataMock = $this->getMockBuilder(
            \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface::class
        )
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->deliveryOptionMock = $this->getMockBuilder(
            \MageWorx\DeliveryDate\Api\DeliveryOptionInterface::class
        )
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $this->deliveryDateValidatorHelperMock = $this->getMockBuilder(
            \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper::class
        )
                                                      ->disableOriginalConstructor()
                                                      ->getMock();

        $this->deliveryDateValidatorDateInPast = new \MageWorx\DeliveryDate\Model\Validator\ValidatePassDate(
            ...['validatorHelper' => $this->deliveryDateValidatorHelperMock]
        );

        $this->deliveryDateValidatorDayLimits = new \MageWorx\DeliveryDate\Model\Validator\ValidateDayLimits(
            ...['validatorHelper' => $this->deliveryDateValidatorHelperMock]
        );

        $args = [
            'validators' => []
        ];

        /** @var \MageWorx\DeliveryDate\Model\Validator\DeliveryDateValidatorPool $model */
        $this->model = new \MageWorx\DeliveryDate\Model\Validator\DeliveryDateValidatorPool(...$args);
    }

    /**
     * Validator runs and throws no exception with no validators
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRun(): void
    {
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInPastValidatorTrue(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('-10 days');
        $validatedDateFormatted = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->model->add($this->deliveryDateValidatorDateInPast);


        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $this->expectException(ValidatorException::class);
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInPastValidatorFalse(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('+10 days');
        $validatedDateFormatted = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $this->model->add($this->deliveryDateValidatorDateInPast);

        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInPastValidatorWithDifferentInputDateFormat(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('+10 days');
        $validatedDateFormatted = $validatedDate->format('m/d/Y');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $this->model->add($this->deliveryDateValidatorDateInPast);

        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery option has no available day limits (slots)
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateWithEmptyDayLimits(): void
    {
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn([]);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->expectException(ValidatorException::class);
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery date selected is after the last available day limit in delivery option
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateDayLimitAfterLastDate(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('+10 days');
        $validatedDateFormatted = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $dayLimits = [
            0 => [
                'date' => $today->format('Y-m-d')
            ], // First day index
            9 => [] // Last day index
        ];
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn($dayLimits);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->expectException(ValidatorException::class);
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery date selected is before the first available day limit in delivery option
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateDayLimitBeforeFirstDate(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('+1 days');
        $validatedDateFormatted = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $dayLimits = [
            3 => [
                'date' => $today->add(new \DateInterval('P3D'))->format('Y-m-d')
            ], // First day index
            9 => [] // Last day index
        ];
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn($dayLimits);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->expectException(ValidatorException::class);
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery date selected in available day limits
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInDayLimits(): void
    {
        $today                  = new \DateTime('now');
        $validatedDate          = new \DateTime('+5 days');
        $validatedDateFormatted = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $dayLimits = [
            3 => [
                'date' => $today->add(new \DateInterval('P3D'))->format('Y-m-d')
            ], // First day index
            4 => [],
            9 => [] // Last day index
        ];
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn($dayLimits);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery date selected is not in day limits (before the first available date on one day)
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInDayLimitsDateIsNotInLimits(): void
    {
        $today                       = new \DateTime('now');
        $validatedDate               = new \DateTime('+1 days');
        $validatedDateFormatted      = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $dayLimits = [
            2 => [
                'date' => $today->add(new \DateInterval('P2D'))->format('Y-m-d')
            ], // First day index
            4 => [],
            9 => [] // Last day index
        ];
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn($dayLimits);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->expectException(ValidatorException::class);
        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }

    /**
     * Delivery date selected is in day limits
     *
     * @return void
     * @throws ValidatorException
     */
    public function testDeliveryDateInDayLimitsDateIsInLimits(): void
    {
        $today                       = new \DateTime('now');
        $validatedDate               = new \DateTime('+1 days');
        $validatedDateFormatted      = $validatedDate->format('Y-m-d');

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getCurrentDateTime')
                                              ->willReturn($today);

        $this->deliveryDateValidatorHelperMock->expects($this->atLeastOnce())
                                              ->method('getSelectedDay')
                                              ->willReturn($validatedDate);

        $this->deliveryDateDataMock->expects($this->atLeastOnce())
                                   ->method('getDeliveryDay')
                                   ->willReturn($validatedDateFormatted);

        $dayLimits = [
            1 => [
                'date' => $today->add(new \DateInterval('P1D'))->format('Y-m-d')
            ], // First day index
            4 => [],
            9 => [] // Last day index
        ];
        $this->deliveryOptionMock->expects($this->atLeastOnce())
                                 ->method('getDayLimits')
                                 ->willReturn($dayLimits);

        $this->model->add($this->deliveryDateValidatorDayLimits);

        $this->model->validateAll($this->deliveryDateDataMock, $this->deliveryOptionMock);
    }
}
