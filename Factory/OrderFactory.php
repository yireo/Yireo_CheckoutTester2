<?php declare(strict_types=1);
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_EmailTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Factory;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderFactory
{
    /**
     * OrderFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return $this->objectManager->create(OrderInterface::class);
    }
}
