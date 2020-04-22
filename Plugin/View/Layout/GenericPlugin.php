<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Plugin\View\Layout;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\View\Layout\Generic as LayoutGeneric;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form as ComponentForm;
use ClassyLlama\AvaTax\Api\UiComponentV1Interface;
use ClassyLlama\AvaTax\Api\UiComponentV2Interface;
use Magento\Customer\Ui\Component\Form\AddressFieldset as FormAddressFieldset;
use Magento\Ui\Component\Form\Fieldset as FormFieldset;

/**
 * Class GenericPlugin
 * @package ClassyLlama\AvaTax\Plugin\View\Layout
 */
class GenericPlugin
{
    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * GenericPlugin constructor.
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(AvaTaxLogger $avaTaxLogger)
    {
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * @param LayoutGeneric $subject
     * @param UiComponentInterface $component
     */
    public function beforeBuild(LayoutGeneric $subject, UiComponentInterface $component): void
    {
        if ($component instanceof ComponentForm) {
            // magento <= 2.3.0
            if ("customer_address_form" === (string)$component->getName() && false === $this->isMarkerInterfaceExists()) {
                /** @var FormFieldset|null $child */
                $child = $component->getComponent('general');
                if (null !== $child) {
                    $this->processComponents($child, UiComponentV2Interface::class);
                }
            }
            // magento >= 2.3.1
            if ("customer_form" === (string)$component->getName() && true === $this->isMarkerInterfaceExists()) {
                /** @var FormAddressFieldset|null $child */
                $child = $component->getComponent('address');
                if (null !== $child) {
                    $this->processComponents($child, UiComponentV1Interface::class);
                }
            }
        }
    }

    /**
     * @param UiComponentInterface $childComponent
     * @param string $markerInterface
     */
    private function processComponents(UiComponentInterface $childComponent, $markerInterface = ''): void
    {
        try {
            if (!empty($markerInterface)) {
                /** @var \ReflectionClass $class */
                $class = new \ReflectionClass(get_class($childComponent));
                if (true === (bool)$class->hasProperty('components')) {
                    /** @var \ReflectionProperty $componentsProperty */
                    $componentsProperty = $class->getProperty('components');
                    $componentsProperty->setAccessible(true);
                    $components = $componentsProperty->getValue($childComponent);
                    /**
                     * @var string $name
                     * @var UiComponentInterface $object
                     */
                    foreach ($components as $name => $object) {
                        if ($object instanceof $markerInterface) {
                            unset($components[$name]);
                        }
                    }
                    $componentsProperty->setValue($childComponent, $components);
                }
            }
        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }

    /**
     * Check the existence of marker interface. It was introduced since Magento 2.3.1
     *
     * @return bool
     */
    private function isMarkerInterfaceExists(): bool
    {
        return (bool)interface_exists(\Magento\Framework\View\Element\ComponentVisibilityInterface::class);
    }
}
