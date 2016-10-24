<?php

namespace Pim\Bundle\IcecatConnectorBundle\Measure;

use Pim\Bundle\IcecatConnectorBundle\Model\Measure;
use Prewk\XmlStringStreamer;

class MeasureRepository
{
    /** @var Measure[] */
    protected $measures;

    /**
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        $this->measures = $this->load($filepath);
    }

    /**
     * @param int $id
     *
     * @return Measure
     */
    public function findById($id)
    {
        return $this->measures[$id];
    }

    /**
     * @param string $sign
     *
     * @return Measure[]
     */
    public function findBySign($sign)
    {
        $result = [];
        foreach ($this->measures as $measure) {
            if ($sign === $measure->getSign()) {
                $result[] = $measure;
            }
        }

        return $result;
    }

    /**
     * @param $filepath
     *
     * @return Measure[]
     */
    protected function load($filepath)
    {
        $streamer = XmlStringStreamer::createStringWalkerParser($filepath, [
            'captureDepth' => 4,
        ]);

        $parser = new MeasureParser();
        $measures = [];

        while ($node = $streamer->getNode()) {
            $simpleXmlNode = simplexml_load_string($node);
            $measure = $parser->parseNode($simpleXmlNode);
            $measures[$measure->getId()] = $measure;
        }

        return $measures;
    }
}
