<?php

namespace Pim\Bundle\IcecatConnectorBundle\Writer\File;

use Pim\Component\Connector\Writer\File\Csv\Writer;

class CsvWriter extends Writer
{
    public function getPath(array $placeholders = [])
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get('output_filepath');

        if (false !== strpos($filePath, '%')) {
            $defaultPlaceholders = ['%datetime%' => date($this->datetimeFormat), '%job_label%' => ''];
            $jobExecution = $this->stepExecution->getJobExecution();

            if (isset($placeholders['%job_label%'])) {
                $placeholders['%job_label%'] = $this->sanitize($placeholders['%job_label%']);
            } elseif (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_label%'] = $this->sanitize($jobExecution->getJobInstance()->getLabel());
            }
            $replacePairs = array_merge($defaultPlaceholders, $placeholders);
            $filePath = strtr($filePath, $replacePairs);
        }

        return $filePath;
    }
}
