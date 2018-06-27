<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model;

class ConfigPlugin
{

    public function after__call( \Magento\Config\Model\Config $subject, $result, $methodName )
    {
        if($methodName !== 'getGroups') {
            return $result;
        }

        if (isset( $result['avatax_general']['fields']['development_company_id']['inherit'] ))
        {
            $result['avatax']['fields']['development_company_code']['inherit'] = $result['avatax_general']['fields']['development_company_id']['inherit'];
        }

        if (isset( $result['avatax_general']['fields']['production_company_id']['inherit'] ))
        {
            $result['avatax']['fields']['production_company_code']['inherit'] = $result['avatax_general']['fields']['production_company_id']['inherit'];
        }

        return $result;
    }
}