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

interface CrossBorderClassInterface
{
    const ID = 'class_id';
    const DESTINATION_COUNTRIES = 'destination_countries';
    const CROSS_BORDER_TYPE = 'cross_border_type_id';
    const HS_CODE = 'hs_code';
    const UNIT_NAME = 'unit_name';
    const UNIT_AMOUNT = 'unit_amount_product_attr';
    const PREF_PROGRAM_IND = 'pref_program_indicator';
    const NO_CROSS_BORDER_TYPE_TEXT = 'Unknown';

    /**
     * Get cross border class ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set cross border class ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get destination country codes applicable to this class
     *
     * @return array|null
     */
    public function getDestinationCountries();

    /**
     * Set destination country codes
     *
     * @param array|null $countries
     * @return $this
     */
    public function setDestinationCountries($countries);

    /**
     * Get the cross border type ID associated with this class
     *
     * @return int|null
     */
    public function getCrossBorderTypeId();

    /**
     * Set the cross border type ID
     *
     * @param int|null $id
     * @return $this
     */
    public function setCrossBorderTypeId($id);

    /**
     * Get the HS code
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
     * Get the unit name, if one is defined
     *
     * @return string|null
     */
    public function getUnitName();

    /**
     * Set the unit name
     *
     * @param string|null $name
     * @return $this
     */
    public function setUnitName($name);

    /**
     * Get the code for the product attribute used to specify unit amount
     *
     * @return string|null
     */
    public function getUnitAmountAttrCode();

    /**
     * Set the code for the product attribute used to specify unit amount
     *
     * @param string|null $attrCode
     * @return $this
     */
    public function setUnitAmountAttrCode($attrCode);

    /**
     * Get the assigned pref. program indicator
     *
     * @return string|null
     */
    public function getPrefProgramIndicator();

    /**
     * Set the assigned pref. program indicator
     *
     * @param string|null $indicator
     * @return $this
     */
    public function setPrefProgramIndicator($indicator);
}