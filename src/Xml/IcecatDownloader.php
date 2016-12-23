<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;

/**
 * Download Icecat XML files
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IcecatDownloader
{
    /** @const string Icecat base url */
    const URI_FREEXML_REFS = 'http://data.icecat.biz/export/freexml.int/refs/';

    /** @var HttpClient */
    protected $httpClient;

    /**
     * IcecatDownloader constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $basename
     * @param string $targetDirectory
     * @param bool   $gunzip
     *
     * @return string
     */
    public function download($basename, $targetDirectory, $gunzip)
    {
        $featureListUri = sprintf('%s%s', self::URI_FREEXML_REFS, $basename);
        $targetFile = $targetDirectory . '/' . $basename;
        $guzzle = $this->httpClient->getGuzzle();
        $guzzle->request('GET', $featureListUri, [
            'auth' => $this->httpClient->getCredentials(),
            'sink' => $targetFile,
        ]);

        $outputPath = $targetFile;
        if ($gunzip) {
            $outputPath = $this->uncompress($targetFile);
        }

        return $outputPath;
    }

    /**
     * @param string $sourceFile compressed source file
     *
     * @return mixed Uncompressed output file path
     */
    protected function uncompress($sourceFile)
    {
        $input = gzopen($sourceFile, 'rb');
        $outputPath = str_replace('.gz', '', basename($sourceFile));
        $ouput = fopen($outputPath, 'wb');

        while (!gzeof($input)) {
            fwrite($ouput, gzread($input, 4096));
        }

        fclose($ouput);
        gzclose($input);

        return $outputPath;
    }
}
