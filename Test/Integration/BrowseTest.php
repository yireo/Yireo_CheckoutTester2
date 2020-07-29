<?php
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_EmailTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration;

use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Yireo\CheckoutTester2\Exception\ForbiddenAccess;
use Yireo\CheckoutTester2\Exception\InvalidOrderId;
use Laminas\Stdlib\Parameters;

/**
 * Class BrowseTest
 *
 * @package Yireo\CheckoutTester2\Test\Integration
 */
class BrowseTest extends ControllerTestCase
{
    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 0
     */
    public function testIfPageFailsIfDisabled()
    {
        $this->expectException(ForbiddenAccess::class);
        $this->dispatch('/checkouttester/index/success');
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIfPageWorksByDefault()
    {
        $this->dispatch('/checkouttester/index/success');
        $body = (string)$this->getResponse()->getBody();
        $this->assertNotEmpty($body);
        $this->assertTrue((bool)strpos($body, 'Thank you for your purchase!'));
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageFailsWithoutValidOrder()
    {
        $this->expectException(InvalidOrderId::class);
        $this->dispatch('/checkouttester/index/success');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture current_store checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithWrongIpSettings()
    {
        $this->expectException(ForbiddenAccess::class);
        $this->getRequest()->setServer(new Parameters(['HTTP_CLIENT_IP' => '1.1.1.2']));
        $this->dispatch('/checkouttester/index/success');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture current_store checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithCorrectIpSettings()
    {
        $this->getRequest()->setServer(new Parameters(['HTTP_CLIENT_IP' => '1.1.1.1']));
        $this->dispatch('/checkouttester/index/success');
        $body = (string)$this->getResponse()->getBody();
        $this->assertNotEmpty($body);
        $this->assertTrue((bool)strpos($body, 'Thank you for your purchase!'));
    }
}
