<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Helper\Partial;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer as View;
use LaminasTest\View\Helper\TestAsset\Aggregate;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Partial view helper.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PartialTest extends TestCase
{
    /**
     * @var Partial
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper   = new Partial();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->helper);
    }

    /**
     * @return void
     */
    public function testPartialRendersScript()
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialOne.phtml');
        $this->assertStringContainsString('This is the first test partial', $return);
    }

    /**
     * @return void
     */
    public function testPartialRendersScriptWithVars()
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $view->vars()->message = 'This should never be read';
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialThree.phtml', ['message' => 'This message should be read']);
        $this->assertStringNotContainsString('This should never be read', $return);
        $this->assertStringContainsString('This message should be read', $return, $return);
    }

    /**
     * @return void
     */
    public function testSetViewSetsViewProperty()
    {
        $view = new View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->getView());
    }

    public function testObjectModelWithPublicPropertiesSetsViewVariables(): void
    {
        $model = new \stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach (get_object_vars($model) as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testObjectModelWithToArraySetsViewVariables(): void
    {
        $model = new Aggregate();

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model->toArray() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        $this->assertSame($this->helper, $test);
    }

    public function testCanPassViewModelAsSecondArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model->getVariables() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testCanPassArrayObjectAsSecondArgument(): void
    {
        $model = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testCanPassViewModelAsSoleArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $model->setTemplate('partialVars.phtml');

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke($model);

        foreach ($model->getVariables() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }
}
