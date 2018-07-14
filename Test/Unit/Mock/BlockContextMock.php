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

namespace Yireo\CheckoutTester2\Test\Unit\Mock;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class DataTest
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContextMock
 */
trait BlockContextMock
{
    /**
     * Import mocking behaviour
     */
    use \Yireo\CheckoutTester2\Test\Unit\Mock\Generic\ScopeConfigMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\Generic\EventManagerMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\Generic\StoreManagerMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\Generic\UrlBuilderMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\Generic\SessionMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext\SidResolverMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext\AppStateMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext\CacheStateMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext\CacheMock;
    use \Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext\FileResolverMock;

    /**
     * Test whether the class can be converted to HTML
     */
    public function testBlockOutput()
    {
        $target = $this->getTarget();
        $target->setData('cache_lifetime', 42);
        $this->assertNotEmpty($target->toHtml());

        $target = $this->getTarget();
        $this->assertEmpty($target->toHtml());
    }

    /**
     *
     */
    public function testBlockOutputIfOutputDisabled()
    {
        $this->setScopeConfigValue('advanced/modules_disable_output/Yireo_CheckoutTester2', 1);
        $target = $this->getTarget();
        $this->assertEmpty($target->toHtml());
    }

    /**
     * @return TemplateContext|Context
     */
    protected function getContextMock($area = 'frontend')
    {
        $contextClass = TemplateContext::class;
        if ($area == 'adminhtml') {
            $contextClass = Context::class;
        }

        $context = $this->createMock(
            $contextClass,
            [],
            [],
            '',
            false,
            false
        );

        $scopeConfig = $this->getScopeConfigMock();
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));

        $eventManager = $this->getEventManagerMock();
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $context->expects($this->any())
            ->method('getCache')
            ->willReturn($this->getCacheMock());

        $context->expects($this->any())
            ->method('getCacheState')
            ->willReturn($this->getCacheStateMock());

        $context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->getStoreManagerMock());

        $context->expects($this->any())
            ->method('getAppState')
            ->willReturn($this->getAppStateMock());

        $context->expects($this->any())
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $context->expects($this->any())
            ->method('getResolver')
            ->willReturn($this->getResolverMock());

        $context->expects($this->any())
            ->method('getSidResolver')
            ->willReturn($this->getSidResolverMock());

        $context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->getUrlBuilderMock());

        return $context;
    }
}
