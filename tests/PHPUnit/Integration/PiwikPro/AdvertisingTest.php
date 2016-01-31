<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\PiwikPro;

use Piwik\Config;
use Piwik\PiwikPro\Advertising;
use Piwik\Plugin;
use Piwik\Tests\Framework\Mock\FakeConfig;
use Piwik\Tests\Framework\Mock\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group PiwikPro
 * @group Advertising
 * @group Integration
 */
class AdvertisingTest extends IntegrationTestCase
{
    /**
     * @var Advertising
     */
    private $advertising;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $pluginManager;

    public function setUp()
    {
        $this->config = new FakeConfig(array('General' => array('piwik_pro_ads_enabled' => '1')));
        $this->pluginManager = new Manager();

        $this->advertising = $this->buildAdvertising($this->config);
    }

    public function test_arePiwikProAdsEnabled_ActuallyEnabled()
    {
        $enabled = $this->advertising->arePiwikProAdsEnabled();

        $this->assertTrue($enabled);
    }

    public function test_arePiwikProAdsEnabled_Disabled()
    {
        $this->config->General = array('piwik_pro_ads_enabled' => '0');

        $enabled = $this->advertising->arePiwikProAdsEnabled();

        $this->assertFalse($enabled);
    }

    public function test_arePiwikProAdsEnabled_shouldBeDisabledWhenCloudPluginIsInstalled()
    {
        $enabled = $this->advertising->arePiwikProAdsEnabled();
        $this->assertTrue($enabled);

        $this->pluginManager->setInstalledPlugins(array('CloudAdmin'));

        $enabled = $this->advertising->arePiwikProAdsEnabled();
        $this->assertFalse($enabled);
    }

    public function test_arePiwikProAdsEnabled_shouldBeDisabledWhenEnterprisePluginIsInstalled()
    {
        $enabled = $this->advertising->arePiwikProAdsEnabled();
        $this->assertTrue($enabled);

        $this->pluginManager->setInstalledPlugins(array('EnterpriseAdmin'));

        $enabled = $this->advertising->arePiwikProAdsEnabled();
        $this->assertFalse($enabled);
    }

    public function test_shouldBeEnabledByDefault()
    {
        $enabled = $this->buildAdvertising(Config::getInstance());

        $this->assertTrue($enabled->arePiwikProAdsEnabled());
    }

    public function test_getPromoLinkForOnPremises_WithoutContent()
    {
        $link = $this->advertising->getPromoLinkForOnPremises('Installation_End');

        $this->assertSame('https://piwik.pro/c/upgrade/?pk_campaign=Upgrade_to_Pro&pk_medium=Installation_End&pk_source=Piwik_App', $link);
    }

    public function test_getPromoLinkForOnPremises_WithContent()
    {
        $link = $this->advertising->getPromoLinkForOnPremises('Installation_End', 'TestContent');

        $this->assertSame('https://piwik.pro/c/upgrade/?pk_campaign=Upgrade_to_Pro&pk_medium=Installation_End&pk_source=Piwik_App&pk_content=TestContent', $link);
    }

    public function test_getPromoLinkForCloud_WithoutContent()
    {
        $link = $this->advertising->getPromoLinkForCloud('Installation_End');

        $this->assertSame('https://piwik.pro/cloud/?pk_campaign=Upgrade_to_Cloud&pk_medium=Installation_End&pk_source=Piwik_App', $link);
    }

    public function test_getPromoLinkForCloud_WithContent()
    {
        $link = $this->advertising->getPromoLinkForCloud('Installation_End', 'TestContent');

        $this->assertSame('https://piwik.pro/cloud/?pk_campaign=Upgrade_to_Cloud&pk_medium=Installation_End&pk_source=Piwik_App&pk_content=TestContent', $link);
    }

    public function test_getCampaignParametersForPromoLink_withoutContent()
    {
        $link = $this->advertising->getCampaignParametersForPromoLink('MyName', 'Installation_Start');

        $this->assertSame('pk_campaign=MyName&pk_medium=Installation_Start&pk_source=Piwik_App', $link);
    }

    public function test_getCampaignParametersForPromoLink_withContent()
    {
        $link = $this->advertising->getCampaignParametersForPromoLink('MyName', 'Installation_Start', 'MyContent');

        $this->assertSame('pk_campaign=MyName&pk_medium=Installation_Start&pk_source=Piwik_App&pk_content=MyContent', $link);
    }

    private function buildAdvertising($config)
    {
        return new Advertising($this->pluginManager, $config);
    }
}
