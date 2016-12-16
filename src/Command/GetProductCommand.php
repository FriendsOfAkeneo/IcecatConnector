<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\IcecatConnectorBundle\Xml\XmlDecodeException;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class GetProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:api:product');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->write($output, '<info>Fetch PIM products with EAN</info>');

        $pqb = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory')
            ->create([])->addFilter('ean', 'NOT EMPTY', null);

        $cursor = $pqb->execute();

        if (count($cursor) === 0) {
            $this->write($output, '<info>No product found</info>');
        }

        while ($cursor->valid()) {
            $product = $cursor->current();
            $this->updateProduct($product, $output);
            $cursor->next();
        }

    }

    protected function updateProduct(ProductInterface $product, OutputInterface $output)
    {
        $ean = $product->getValue('ean');
        $this->write($output, sprintf('Get Icecat product EAN <info>%s</info>', $ean));

        $httpClient = $this->getContainer()->get('pim_icecat_connector.http.client');

        $endpoint = $this->getContainer()->getParameter('pim_icecat_connector.endpoint.product.ean');
        $query = sprintf('?' . $endpoint, $ean);

        $guzzle = $httpClient->getGuzzle();
        $res = $guzzle->request('GET', $query, [
            'auth' => $httpClient->getCredentials(),
        ]);
        $decoder = $this->getContainer()->get('pim_icecat_connector.decoder.xml.product');
        try {
            $standardProduct = $decoder->decode($res->getBody()->getContents(), 'xml');
            $updater = $this->getContainer()->get('pim_catalog.updater.product');
            $updater->update($product, $standardProduct);
            $saver = $this->getContainer()->get('pim_catalog.saver.product');
            $saver->save($product);
        } catch (XmlDecodeException $e) {
            $this->write($output, $e->getMessage());
            $this->write($output, $e->getPrevious()->getMessage());
        } catch (\Exception $e) {
            $this->write($output, $e->getMessage());
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function write(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }
}
