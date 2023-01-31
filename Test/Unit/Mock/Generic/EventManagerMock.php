<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Mock\Generic;

use Magento\Framework\Event\ManagerInterface;

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
