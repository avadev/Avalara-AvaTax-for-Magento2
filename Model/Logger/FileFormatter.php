<?php

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Formats incoming records into a string
 *
 * @author Matt Johnson <matt.johnson@classyllama.com>
 */
class FileFormatter extends LineFormatter
{

    /**
     * @var LineFormatter
     */
    private $normalizerFormatter;

    public function __construct()
    {
        parent::__construct(null, null, true);
        $this->normalizerFormatter = new NormalizerFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = $this->normalizerFormatter->format($record);

        $output = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', var_export($val, 1), $output);
                unset($vars['extra'][$var]);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $val_output = '';
                if ((is_array($val) && count($val) > 0) || is_array($val) === false) {
                    $val_output = var_export($val, 1);
                }
                $output = str_replace('%'.$var.'%', $val_output, $output);
            }
        }

        return $output;
    }
}