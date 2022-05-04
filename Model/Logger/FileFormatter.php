<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Formats incoming records similar to LineFormatter
 * but allows for new line characters in the context, and extra parts of the record
 * and prints them on multiple lines instead of condensing those sections to a single line.
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
    public function format(array $record): string
    {
        $vars = $this->normalizerFormatter->format($record);

        $output = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.' . $var . '%', var_export($val, true), $output);
                unset($vars['extra'][$var]);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $val_output = '';
                if ((is_array($val) && count($val) > 0) || is_array($val) === false) {
                    $val_output = var_export($val, true);
                }
                $output = str_replace('%' . $var . '%', $val_output, $output);
            }
        }

        return $output;
    }
}
