<?php

namespace ClassyLlama\AvaTax\Component\Layout;

use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * @codeCoverageIgnore
 */
class Tabs extends \Magento\Ui\Component\Layout\Tabs
{
    /**
     * Add children data
     *
     * @param array                $topNode
     * @param UiComponentInterface $component
     * @param string               $componentType
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addChildren(array &$topNode, UiComponentInterface $component, $componentType)
    {
        $childrenAreas = [];
        $collectedComponents = [];

        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof DataSourceInterface) {
                continue;
            }
            /* BEGIN EDIT */
            /**
             * This is required to allow usage of UI components in the customer form, but also allow the tab to be
             * shown/hidden dynamically based on configuration and if there is a valid customer (i.e. customer creation)
             * Without this, the only way to hide a tab is to use the htmlContent component. That component, however,
             * does not allow the embedding of another UI component. This is the simplest way to achieve dynamic tab
             * display and still preserve UI component usage
             */
            if ($childComponent instanceof TabInterface && !$childComponent->canShowTab()) {
                continue;
            }
            /* END EDIT */

            if ($childComponent instanceof BlockWrapperInterface) {
                $this->addWrappedBlock($childComponent, $childrenAreas);
                continue;
            }

            $name = $childComponent->getName();
            $config = $childComponent->getData('config');
            $collectedComponents[$name] = true;
            if (isset($config['is_collection']) && $config['is_collection'] === true) {
                $label = $childComponent->getData('config/label');
                $this->component->getContext()->addComponentDefinition(
                    'collection',
                    [
                        'component' => 'Magento_Ui/js/form/components/collection',
                        'extends' => $this->namespace
                    ]
                );

                /**
                 * @var UiComponentInterface $childComponent
                 * @var array                $structure
                 */
                list($childComponent, $structure) = $this->prepareChildComponents($childComponent, $name);

                $childrenStructure = $structure[$name]['children'];

                $structure[$name]['children'] = [
                    $name . '_collection' => [
                        'type' => 'collection',
                        'config' => [
                            'active' => 1,
                            'removeLabel' => __('Remove %1', $label),
                            'addLabel' => __('Add New %1', $label),
                            'removeMessage' => $childComponent->getData('config/removeMessage'),
                            'itemTemplate' => 'item_template',
                        ],
                        'children' => [
                            'item_template' => [
                                'type' => $this->namespace,
                                'isTemplate' => true,
                                'component' => 'Magento_Ui/js/form/components/collection/item',
                                'childType' => 'group',
                                'config' => [
                                    'label' => __('New %1', $label),
                                ],
                                'children' => $childrenStructure
                            ]
                        ]
                    ]
                ];
            } else {
                /**
                 * @var UiComponentInterface $childComponent
                 * @var array                $structure
                 */
                list($childComponent, $structure) = $this->prepareChildComponents($childComponent, $name);
            }

            $tabComponent = $this->createTabComponent($childComponent, $name);

            if (isset($structure[$name]['dataScope']) && $structure[$name]['dataScope']) {
                $dataScope = $structure[$name]['dataScope'];
                unset($structure[$name]['dataScope']);
            } else {
                $dataScope = 'data.' . $name;
            }

            $childrenAreas[$name] = [
                'type' => $tabComponent->getComponentName(),
                'dataScope' => $dataScope,
                'config' => $config,
                'insertTo' => [
                    $this->namespace . '.sections' => [
                        'position' => $this->getNextSortIncrement()
                    ]
                ],
                'children' => $structure,
            ];
        }

        $this->structure[static::AREAS_KEY]['children'] = $childrenAreas;
        $topNode = $this->structure;
    }
}
