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

namespace ClassyLlama\AvaTax\Plugin\Model;

use Magento\Config\Model\Config;

class ConfigPlugin
{

    public function around__call(Config $subject, $proceed, $methodName, $args)
    {
        $result = $proceed($methodName, $args);

        if ($methodName !== 'getGroups') {
            return $result;
        }

        if (isset($result['avatax_general']['fields']['development_company_id']['inherit'])) {
            $result['avatax']['fields']['development_company_code']['inherit'] = $result['avatax_general']['fields']['development_company_id']['inherit'];
        }

        if (isset($result['avatax_general']['fields']['production_company_id']['inherit'])) {
            $result['avatax']['fields']['production_company_code']['inherit'] = $result['avatax_general']['fields']['production_company_id']['inherit'];
        }

        return $result;
    }
}
