<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\Paginator;
use Laminas\View\Exception;
use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PaginationControlTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * @var Helper\PaginationControl
     */
    private $_viewHelper;

    private $_paginator;
    // @codingStandardsIgnoreEnd

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->markTestIncomplete('Re-enable after laminas-paginator is updated to laminas-servicemanager v3');

        $resolver = new Resolver\TemplatePathStack(['script_paths' => [
            __DIR__ . '/_files/scripts',
        ]]);
        $view = new View();
        $view->setResolver($resolver);

        Helper\PaginationControl::setDefaultViewPartial(null);
        $this->_viewHelper = new Helper\PaginationControl();
        $this->_viewHelper->setView($view);
        $adapter = new Paginator\Adapter\ArrayAdapter(range(1, 101));
        $this->_paginator = new Paginator\Paginator($adapter);
    }

    protected function tearDown(): void
    {
        unset($this->_viewHelper);
        unset($this->_paginator);
    }

    public function testGetsAndSetsView(): void
    {
        $view   = new View();
        $helper = new Helper\PaginationControl();
        $this->assertNull($helper->getView());
        $helper->setView($view);
        $this->assertInstanceOf(RendererInterface::class, $helper->getView());
    }

    public function testGetsAndSetsDefaultViewPartial(): void
    {
        $this->assertNull(Helper\PaginationControl::getDefaultViewPartial());
        Helper\PaginationControl::setDefaultViewPartial('partial');
        $this->assertEquals('partial', Helper\PaginationControl::getDefaultViewPartial());
        Helper\PaginationControl::setDefaultViewPartial(null);
    }

    public function testUsesDefaultViewPartialIfNoneSupplied(): void
    {
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');
        $output = $this->_viewHelper->__invoke($this->_paginator);
        $this->assertStringContainsString('pagination control', $output, $output);
        Helper\PaginationControl::setDefaultViewPartial(null);
    }

    public function testThrowsExceptionIfNoViewPartialFound(): void
    {
        try {
            $this->_viewHelper->__invoke($this->_paginator);
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception\ExceptionInterface::class, $e);
            $this->assertEquals('No view partial provided and no default set', $e->getMessage());
        }
    }

    /**
     * @group Laminas-4037
     *
     * @return void
     */
    public function testUsesDefaultScrollingStyleIfNoneSupplied(): void
    {
        // First we'll make sure the base case works
        $output = $this->_viewHelper->__invoke($this->_paginator, 'All', 'testPagination.phtml');
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);

        Paginator\Paginator::setDefaultScrollingStyle('All');
        $output = $this->_viewHelper->__invoke($this->_paginator, null, 'testPagination.phtml');
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);

        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');
        $output = $this->_viewHelper->__invoke($this->_paginator);
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);
    }

    /**
     * @group Laminas-4153
     *
     * @return void
     */
    public function testUsesPaginatorFromViewIfNoneSupplied(): void
    {
        $this->_viewHelper->getView()->paginator = $this->_paginator;
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $output = $this->_viewHelper->__invoke();

        $this->assertStringContainsString('pagination control', $output, $output);
    }

    /**
     * @group Laminas-4153
     *
     * @return void
     */
    public function testThrowsExceptionIfNoPaginatorFound(): void
    {
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $this->expectException(Exception\ExceptionInterface::class);
        $this->expectExceptionMessage('No paginator instance provided or incorrect type');
        $this->_viewHelper->__invoke();
    }

    /**
     * @group Laminas-4233
     *
     * @return void
     */
    public function testAcceptsViewPartialInOtherModule(): void
    {
        try {
            $this->_viewHelper->__invoke($this->_paginator, null, ['partial.phtml', 'test']);
        } catch (\Exception $e) {
            /* We don't care whether or not the module exists--we just want to
             * make sure it gets to Laminas_View_Helper_Partial and it's recognized
             * as a module. */
            $this->assertInstanceOf(
                Exception\RuntimeException::class,
                $e,
                sprintf(
                    'Expected View RuntimeException; received "%s" with message: %s',
                    get_class($e),
                    $e->getMessage()
                )
            );
            $this->assertStringContainsString('could not resolve', $e->getMessage());
        }
    }

    /**
     * @group Laminas-4328
     *
     * @return void
     */
    public function testUsesPaginatorFromViewOnlyIfNoneSupplied(): void
    {
        $this->_viewHelper->getView()->vars()->paginator  = $this->_paginator;
        $paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 30)));
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $output = $this->_viewHelper->__invoke($paginator);
        $this->assertStringContainsString('page count (3)', $output, $output);
    }

    /**
     * @group Laminas-4878
     *
     * @return void
     */
    public function testCanUseObjectForScrollingStyle(): void
    {
        $all = new Paginator\ScrollingStyle\All();

        $output = $this->_viewHelper->__invoke($this->_paginator, $all, 'testPagination.phtml');

        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);
    }
}
