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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Api\Data;

interface ProductCrossBorderDetailsInterface
{
    /**
     * Get the product ID
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set the product ID
     *
     * @param int $id
     * @return $this
     */
    public function setProductId($id);

    /**
     * Get the particular destination country associated with these details
     *
     * @return string
     */
    public function getDestinationCountry();

    /**
     * Set the destination country associated with these details
     *
     * @param string $country
     * @return $this
     */
    public function setDestinationCountry($country);

    /**
     * Get the assigned HS code
     *
     * @return string
     */
    public function getHsCode();

    /**
     * Set the HS code
     *
     * @param string $code
     * @return $this
     */
    public function setHsCode($code);

    /**
     * Get the mass unit name (e.g., kg)
     *
     * @return string
     */
    public function getUnitName();

    /**
     * Set the mass unit name
     *
     * @param string $unit
     * @return $this
     */
    public function setUnitName($unit);

    /**
     * Get the code of the product attribute for unit amount value
     *
     * @return string
     */
    public function getUnitAmountAttrCode();

    /**
     * Set the unit amount product attribute code
     *
     * @param string $code
     * @return $this
     */
    public function setUnitAmountAttrCode($code);

    /**
     * Get the pref. program indicator
     *
     * @return string
     */
    public function getPrefProgramIndicator();

    /**
     * Set the pref. program indicator
     *
     * @param string $indicator
     * @return $this
     */
    public function setPrefProgramIndicator($indicator);
}