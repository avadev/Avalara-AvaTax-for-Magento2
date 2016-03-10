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

namespace ClassyLlama\AvaTax\Model;

/**
 * Data formatter
 */
class DataFormatter
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    ) {
        $this->dateFactory = $dateFactory;
    }

    /**
     * Get calculated time passed since now until specified date
     *
     * Current time should be always greater or equal to specified date
     *
     * @param string $dateString
     * @return string
     */
    public function getSinceTimeString($dateString)
    {
        $dateTime = $this->dateFactory->create()->gmtDate();
        $timeDiff = strtotime($dateTime) - strtotime($dateString);
        if ($timeDiff < 0) {
            return '';
        }
        if ($timeDiff == 0) {
            return __('(now)');
        }

        return $this->getTimeDiffMessage($timeDiff);
    }

    /**
     * Get time diff message base on time diff
     *
     * @param int $timeDiff
     * @return \Magento\Framework\Phrase
     */
    protected function getTimeDiffMessage($timeDiff)
    {
        $timeRanges = [
            'minutes' => ['min' => 0, 'max' => 3540, 'div' => 60],
            'hours' => ['min' => 3540, 'max' => 82800, 'div' => 3600],
            'days' => ['min' => 82800, 'max' => 518400, 'div' => 86400],
            'weeks' => ['min' => 518400, 'max' => 1814400, 'div' => 604800],
            'months' => ['min' => 1814400, 'max' => 28512000, 'div' => 2592000],
            'years' => ['min' => 28512000, 'div' => 31536000]
        ];

        $value = 0;
        $type = 'minutes';
        foreach ($timeRanges as $type => $timeData) {
            if ($timeDiff > $timeData['min'] && (!isset($timeData['max']) || $timeDiff <= $timeData['max'])) {
                $value = round($timeDiff / $timeData['div']);
                break;
            }
        }
        $value = $value ?: 1;

        $messages = [
            'minutes' => __('[%1 minutes ago]', $value),
            'hours' => __('[%1 hours ago]', $value),
            'days' => __('[%1 days ago]', $value),
            'weeks' => __('[%1 weeks ago]', $value),
            'months' => __('[%1 months ago]', $value),
            'years' => __('[%1 years ago]', $value)
        ];

        return isset($messages[$type]) ? $messages[$type] : $messages['years'];
    }

    /**
     * Format specified bits|bytes to human readable string
     *
     * E.g., following code return string "1.05 MiB":
     * <code>
     * <?php
     *      $dataFormatter->formatBytes('1048576', 3, 'SI', 'B');
     * ?>
     * </code>
     *
     * And following code return string "1 MB":
     * <code>
     * <?php
     *      $dataFormatter->formatBytes('1048576', 3, 'IEC', 'B');
     * ?>
     * </code>
     *
     * @param int $val
     * @param int $digits how match digits must be used when round result
     * @param string $mode SI'|'IEC': if SI, then division factor will be 1000, other way - 1024
     * @param string $bB 'b'|'B': if b, then result will be in bits, other way in bytes
     *
     * @return string
     */
    public function formatBytes($val, $digits = 3, $mode = 'SI', $bB = 'B')
    {
        $iec = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
        $si = ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'];
        $nums = 9;
        $mode = strtoupper((string)$mode);
        $mode = $mode != 'SI' && $mode != 'IEC' ? 'SI' : $mode;
        if ($mode == 'SI') {
            $factor = 1000;
            $symbols = $si;
        } else {
            $factor = 1024;
            $symbols = $iec;
        }
        if ($bB == 'b') {
            $val *= 8;
        } else {
            $bB = 'B';
        }
        for ($i=0; $i < $nums - 1 && $val >= $factor; $i++) {
            $val /= $factor;
        }
        $val = $this->roundNumLeavingDigits($val, $digits);
        return $val . ' ' . $symbols[$i] . $bB;
    }

    /**
     * Round number leaving specified amount of digits
     *
     * @param float $value
     * @param int $digits
     * @return float
     */
    protected function roundNumLeavingDigits($value, $digits)
    {
        $pointPos = strpos($value, '.');
        if ($pointPos !== false) {
            return $pointPos > $digits ? round($value) : round($value, $digits - $pointPos);
        }
        return $value;
    }
}
