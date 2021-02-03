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

namespace ClassyLlama\AvaTax\Model\Queue\Processing;


/**
 * Interface ProcessingStrategyInterface
 *
 * @package ClassyLlama\AvaTax\Model\Queue\Processing
 */
interface  ProcessingStrategyInterface
{
    const QUEUE_PROCESSING_DELAY = 2 * 60;
    /**
     * @return mixed
     */
    public function execute();

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param int $limit
     * @return mixed
     */
    public function setLimit(int $limit);

    /**
     * @return int
     */
    public function getProcessCount(): int;

    /**
     * @return int
     */
    public function getErrorCount(): int;

    /**
     * @return array
     */
    public function getErrorMessages():array;

}
