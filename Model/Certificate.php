<?php
/**
 * Certificate.php
 *
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

class Certificate implements \ClassyLlama\Avatax\Api\CertificateInterface
{

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Certificate constructor.
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    )
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(int $certificateId)
    {
        try {
            $certCaptureConfig = $this->deploymentConfig->get('cert-capture');

            if (!isset(
                $certCaptureConfig['auth']['username'],
                $certCaptureConfig['auth']['password'],
                $certCaptureConfig['sdk-url'],
                $certCaptureConfig['client-id']
            )) {
                return "Invalid Deployment Configuration";
            }

            $auth = base64_encode("{$certCaptureConfig['auth']['username']}:{$certCaptureConfig['auth']['password']}");

            //todo fix url
            $url = $certCaptureConfig['url'] . '/' . $certificateId;

            $client = new \Zend_Http_Client($url);

            $client->setAuth($auth);
            $client->setMethod("DELETE");
            $client->setHeaders([
               "x-client-id" => $certCaptureConfig['client-id']
            ]);

            $response = $client->request();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}