<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Xml;

use Pim\Bundle\IcecatConnectorBundle\Tests\AbstractTestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IcecatDownloaderTest extends AbstractTestCase
{
    public function testXmlIcecatDownloader()
    {
        $featuresListFile = 'FeaturesList.xml.gz';
        if (file_exists($featuresListFile)) {
            unlink($featuresListFile);
        }
        $uncompressedFeaturesListFile = 'FeaturesList.xml';
        if (file_exists($uncompressedFeaturesListFile)) {
            unlink($uncompressedFeaturesListFile);
        }
        $targetDirectory = '/tmp';

        $downloader = $this->get('pim_icecat_connector.xml.downloader');
        $downloadedFile = $downloader->download($featuresListFile, $targetDirectory, true);

        $this->assertTrue(file_exists($targetDirectory . DIRECTORY_SEPARATOR . $featuresListFile));
        $this->assertTrue(file_exists($targetDirectory . DIRECTORY_SEPARATOR . $uncompressedFeaturesListFile));
        $this->assertEquals($targetDirectory . DIRECTORY_SEPARATOR . $uncompressedFeaturesListFile, $downloadedFile);
    }
}
