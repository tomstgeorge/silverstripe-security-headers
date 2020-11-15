<?php

class SecurityHeaderSiteconfigExtensionTest extends FunctionalTest
{
    protected static $fixture_file = 'fixtures.yml';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        // Add extension.
        SiteConfig::add_extension(SecurityHeaderSiteconfigExtension::class);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        // Remove extension.
        SiteConfig::remove_extension(SecurityHeaderSiteconfigExtension::class);
    }

    public function testCSPisNotReportOnly()
    {
        $response = $this->getResponse();
        $csp = $response->getHeader('Content-Security-Policy');
        $cspReportOnly = $response->getHeader('Content-Security-Policy-Report-Only');

        $this->assertNotNull($csp, 'Test Content-Security-Policy header is present.');
        $this->assertNull($cspReportOnly, 'Test Content-Security-Policy-Report-Only header is not present.');
    }

    public function testCSPisReportOnly()
    {
        $config = Config::inst()->forClass(SecurityHeaderRequestFilter::class);
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->CSPReportingOnly = true;
        $siteConfig->write();
        $originalCSP = $config->get('headers')['Content-Security-Policy'];
        $originalCSP .= ' report-uri ' . $config->get('report_uri') . ';';

        $response = $this->getResponse();
        $csp = $response->getHeader('Content-Security-Policy');
        $cspReportOnly = $response->getHeader('Content-Security-Policy-Report-Only');

        $this->assertNull($csp, 'Test Content-Security-Policy header is not present.');
        $this->assertNotNull($cspReportOnly, 'Test Content-Security-Policy-Report-Only header is present.');
        $this->assertEquals($originalCSP, $cspReportOnly, 'Test configured CSP is returned in the response.');
    }

    protected function getResponse()
    {
        $page = $this->objFromFixture('Page', 'page');
        $page->publish(Versioned::DRAFT, Versioned::LIVE);
        return $this->get($page->Link());
    }

}
