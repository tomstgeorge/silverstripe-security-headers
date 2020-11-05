<?php
namespace Signify\SecurityHeaders\Tests;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\SiteConfig\SiteConfig;
use Signify\SecurityHeaders\Extensions\SecurityHeaderSiteconfigExtension;
use SilverStripe\Control\Director;
use Signify\SecurityHeaders\Extensions\SecurityHeaderControllerExtension;

class SecurityHeaderSiteconfigExtensionTest extends FunctionalTest
{
    const HEADER_TEST_ROUTE = 'security-header-test';

    public function setUpOnce()
    {
        // Add extension and a new test route.
        SiteConfig::add_extension(SecurityHeaderSiteconfigExtension::class);
        Director::config()->update('rules', array(
            self::HEADER_TEST_ROUTE => 'Controller'
        ));
    }

    public function tearDownOnce()
    {
        // Remove extension and test route.
        SiteConfig::remove_extension(SecurityHeaderSiteconfigExtension::class);
        Director::config()->remove('rules', self::HEADER_TEST_ROUTE);
    }

    public function testCSPisNotReportOnly()
    {
        $response = $this->get(self::HEADER_TEST_ROUTE);
        $csp = $response->getHeader('Content-Security-Policy');
        $cspReportOnly = $response->getHeader('Content-Security-Policy-Report-Only');

        $this->assertNotNull($csp, 'Test Content-Security-Policy header is present.');
        $this->assertNull($cspReportOnly, 'Test Content-Security-Policy-Report-Only header is not present.');
    }

    public function testCSPisReportOnly()
    {
        SiteConfig::current_site_config()->CSPReportingOnly = true;
        SiteConfig::current_site_config()->write();
        $originalCSP = SecurityHeaderControllerExtension::config()->get('headers')['Content-Security-Policy'];

        $response = $this->get(self::HEADER_TEST_ROUTE);
        $csp = $response->getHeader('Content-Security-Policy');
        $cspReportOnly = $response->getHeader('Content-Security-Policy-Report-Only');

        $this->assertNull($csp, 'Test Content-Security-Policy header is not present.');
        $this->assertNotNull($cspReportOnly, 'Test Content-Security-Policy-Report-Only header is present.');
        $this->assertEquals($originalCSP, $cspReportOnly, 'Test configured CSP is returned in the response.');
    }

}
