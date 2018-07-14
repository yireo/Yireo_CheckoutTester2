<?php
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types = 1);

namespace Yireo\CheckoutTester2\Test\Unit\Mock\Generic;

use Magento\Framework\Event\ManagerInterface;

/**
 * Class EventManagerMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\Generic
 */
trait EventManagerMock
{
    /**
     * @return ManagerInterface
     */
    protected function getEventManagerMock()
    {
        $eventManager = $this->createMock(ManagerInterface::class);

        return $eventManager;
    }
}
