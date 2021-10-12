<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Navigation;

use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Navigation as NavigationHelper;

class AbstractHelperTest extends AbstractTest
{
    // @codingStandardsIgnoreStart
    /**
     * Class name for view helper to test
     *
     * @var string
     */
    protected $_helperName = NavigationHelper::class;

    /**
     * View helper
     *
     * @var NavigationHelper\Breadcrumbs
     */
    protected $_helper;
    // @codingStandardsIgnoreEnd

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->_helper) {
            $this->_helper->setDefaultAcl(null);
            $this->_helper->setAcl(null);
            $this->_helper->setDefaultRole(null);
            $this->_helper->setRole(null);
        }
    }

    public function testHasACLChecksDefaultACL(): void
    {
        $aclContainer = $this->_getAcl();
        $acl = $aclContainer['acl'];

        $this->assertEquals(false, $this->_helper->hasACL());
        $this->_helper->setDefaultAcl($acl);
        $this->assertEquals(true, $this->_helper->hasAcl());
    }

    public function testHasACLChecksMemberVariable(): void
    {
        $aclContainer = $this->_getAcl();
        $acl = $aclContainer['acl'];

        $this->assertEquals(false, $this->_helper->hasAcl());
        $this->_helper->setAcl($acl);
        $this->assertEquals(true, $this->_helper->hasAcl());
    }

    public function testHasRoleChecksDefaultRole(): void
    {
        $aclContainer = $this->_getAcl();
        $role = $aclContainer['role'];

        $this->assertEquals(false, $this->_helper->hasRole());
        $this->_helper->setDefaultRole($role);
        $this->assertEquals(true, $this->_helper->hasRole());
    }

    public function testHasRoleChecksMemberVariable(): void
    {
        $aclContainer = $this->_getAcl();
        $role = $aclContainer['role'];

        $this->assertEquals(false, $this->_helper->hasRole());
        $this->_helper->setRole($role);
        $this->assertEquals(true, $this->_helper->hasRole());
    }

    public function testEventManagerIsNullByDefault(): void
    {
        $this->assertNull($this->_helper->getEventManager());
    }

    public function testFallBackForContainerNames(): void
    {
        // Register navigation service with name equal to the documentation
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'navigation',
            $this->serviceManager->get('Navigation')
        );
        $this->serviceManager->setAllowOverride(false);

        $this->_helper->setServiceLocator($this->serviceManager);

        $this->_helper->setContainer('navigation');
        $this->assertInstanceOf(
            Navigation::class,
            $this->_helper->getContainer()
        );

        $this->_helper->setContainer('default');
        $this->assertInstanceOf(
            Navigation::class,
            $this->_helper->getContainer()
        );
    }
}
