<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use GuzzleHttp\Client;
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
            ->setName('pim-icecat:api:product')
            ->addArgument(
                'ean',
                InputArgument::REQUIRED
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ean = $input->getArgument('ean');
        $ean = '4960999358246';
        $sku = '10699783';

        $this->write($output, sprintf('Fetch PIM product EAN <info>%s</info>', $ean));

        $productRepository = $this->getContainer()->get('pim_catalog.repository.product');
        $product = $productRepository->findOneByIdentifier($sku);

        $this->write($output, sprintf('Get Icecat product EAN <info>%s</info>', $ean));

        $httpClient = $this->getContainer()->get('pim_icecat_connector.http.client');

        $endpoint = $this->getContainer()->getParameter('pim_icecat_connector.endpoint.product.ean');
        $query = sprintf('?' . $endpoint, $ean);

        $guzzle = $httpClient->getGuzzle();
        $res = $guzzle->request('GET', $query, [
            'auth' => $httpClient->getCredentials(),
        ]);
        $decoder = $this->getContainer()->get('pim_icecat_connector.decoder.xml.product');
        $standardProduct = $decoder->decode($res->getBody()->getContents(), 'xml');
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        $updater->update($product, $standardProduct);
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
