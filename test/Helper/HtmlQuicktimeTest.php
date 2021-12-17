<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\HtmlQuicktime;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlQuicktimeTest extends TestCase
{
    /**
     * @var HtmlQuicktime
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->view   = new View();
        $this->helper = new HtmlQuicktime();
        $this->helper->setView($this->view);
    }

    public function testMakeHtmlQuicktime(): void
    {
        $htmlQuicktime = $this->helper->__invoke('/path/to/quicktime.mov');

        $objectStartElement = '<object data="&#x2F;path&#x2F;to&#x2F;quicktime.mov"'
                            . ' type="video&#x2F;quicktime"'
                            . ' classid="clsid&#x3A;02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"'
                            . ' codebase="http&#x3A;&#x2F;&#x2F;www.apple.com&#x2F;qtactivex&#x2F;qtplugin.cab">';

        $this->assertStringContainsString($objectStartElement, $htmlQuicktime);
        $this->assertStringContainsString('</object>', $htmlQuicktime);
    }
}
