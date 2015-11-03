<?php
namespace Presentation\Framework\Component\ManagedList;

use Presentation\Framework\Component\CompoundComponent;
use Presentation\Framework\Component\ManagedList\Control\ControlInterface;
use Presentation\Framework\Base\ComponentInterface;
use Presentation\Framework\Data\ArrayDataProvider;
use Presentation\Framework\Data\DataProviderInterface;
use Traversable;

/**
 * Class ManagedList
 *
 * ManagedList is a component for rendering data lists with interactive controls.
 */
class ManagedList extends CompoundComponent
{

    protected $isOperationsApplied = false;

    /**
     * @var array|null|DataProviderInterface|Traversable
     */
    protected $dataSource;

    protected function getDefaultStructure()
    {
        return [
            'form' => [
                'submit_button'
            ],
            'container' => [
                'repeater' => [
                    'list_item' => []
                ]
            ]
        ];
    }

    /**
     * @param array|null|DataProviderInterface|Traversable $dataSource
     */
    public function setDataSource($dataSource)
    {
        if (!$dataSource instanceof DataProviderInterface && (is_array($dataSource) || $dataSource instanceof Traversable)) {
            $dataSource = new ArrayDataProvider($dataSource);
        }
        $this->dataSource = $dataSource;
    }

    protected function buildTree()
    {
        if ($this->dataSource !== null) {
            $this->components()->getRepeater()->setIterator($this->dataSource);
        }
        return parent::buildTree();
    }

    /**
     * @param array|Traversable|DataProviderInterface|null $dataSrc
     * @param ComponentInterface|null $listItem
     * @param ControlInterface[]|null $controls
     */
    public function __construct(
        $dataSrc = null,
        ComponentInterface $listItem = null,
        $controls = null
    )
    {
        parent::__construct(
            $this->getDefaultStructure()
        );
        $this->setDataSource($dataSrc);
        if (!empty($controls)) {
            $this->components()->getForm()->addChildren($controls);
        }
        $this->components()->setListItem($listItem);
    }

    public function applyOperations()
    {
        $controls = $this->components()->getControls();
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->components()->getRepeater()->getIterator();
        if (!$this->isOperationsApplied) {
            foreach($controls as $control) {
                $dataProvider->operations()->add($control->getOperation());
            }
            $this->isOperationsApplied = true;
        }
    }

    public function render()
    {
        $this->updateTreeIfRequired();
        $this->applyOperations();
        return parent::render();
    }

    /**
     * Returns new instance of component registry and initializes it with components.
     *
     * @param array $components
     * @return Registry
     */
    protected function makeComponentRegistry(array $components = [])
    {
        return new Registry($components);
    }

    /**
     * @return Registry|ComponentInterface[]
     */
    public function components()
    {
        return parent::components();
    }
}