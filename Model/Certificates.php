<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\CertificatesInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use ClassyLlama\AvaTax\Helper\CertificateHelper;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject;
use Magento\User\Model\User;

/**
 * Class Certificates
 * @package ClassyLlama\AvaTax\Model
 */
class Certificates implements CertificatesInterface
{
    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * @var CertificateHelper
     */
    private $certificateHelper;

    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * Certificates constructor.
     * @param AuthSession $authSession
     * @param CertificateHelper $certificateHelper
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        AuthSession $authSession,
        CertificateHelper $certificateHelper,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->authSession = $authSession;
        $this->certificateHelper = $certificateHelper;
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * Get certificates list
     *
     * @param int|null $userId
     * @return array
     */
    public function getCertificatesList(int $userId = null): array
    {
        /** @var User|null $user */
        $user = $this->authSession->getUser();
        /** @var int|null $userId */
        $userId = $userId ?? (null !== $user ? (int)$user->getId() : null);
        $certificates = [];
        try {
            if (null !== $userId) {
                /** @var array<int, DataObject> $certificates */
                $certificates = $this->certificateHelper->getCertificates($userId);
                $certificates = $this->prepareCertificateURLs($certificates, $userId);
            }
        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
        }
        return $certificates;
    }

    /**
     * @param array $certificates
     * @param int|null $userId
     * @return array
     */
    private function prepareCertificateURLs(array $certificates = [], int $userId = null): array
    {
        if (!empty($certificates) && null !== $userId) {
            /** @var DataObject $certificate */
            foreach ($certificates as $certificate) {
                /** @var string $viewUrl */
                $viewUrl = (string)$this->certificateHelper->getCertificateUrl($certificate->getData('id'), $userId);
                /** @var string $deleteUrl */
                $deleteUrl = (string)$this->certificateHelper->getCertificateDeleteUrl($certificate->getData('id'), $userId);
                $certificate->setData('certificate_url', $viewUrl);
                $certificate->setData('certificate_delete_url', $deleteUrl);
            }
        }
        return $certificates;
    }
}
