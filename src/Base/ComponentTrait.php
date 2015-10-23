<?php
namespace Presentation\Framework\Base;

use Nayjest\Collection\Extended\ObjectCollectionInterface;
use Presentation\Framework\Event\BeforeRenderTrait;

trait ComponentTrait
{
    use BeforeRenderTrait;

    protected $componentName;

    protected $sortPosition = 1;

    protected $isSortingEnabled = true;

    /** @return ObjectCollectionInterface */
    abstract public function children();

    public function renderChildren()
    {
        $output = '';
        /** @var ComponentInterface $child */
        foreach ($this->getChildrenForRendering() as $child) {
            $output .= $child->render();
        }
        return $output;
    }

    public function render()
    {
        return $this->beforeRender()->notify()
        . $this->renderChildren();
    }

    /**
     * @return string|null
     */
    public function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * @param string|null $componentName
     * @return $this
     */
    public function setComponentName($componentName)
    {
        $this->componentName = $componentName;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortPosition()
    {
        return $this->sortPosition;
    }

    /**
     * @param int $sortPosition
     * @return $this
     */
    public function setSortPosition($sortPosition)
    {
        $this->sortPosition = $sortPosition;
        return $this;
    }

    /**
     * @param bool $value
     */
    public function setSortable($value = true)
    {
        $this->isSortingEnabled = $value;
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        return $this->isSortingEnabled;
    }


    protected function getChildrenForRendering()
    {
        return $this->isSortingEnabled ? $this->children()->sortByProperty('sortPosition') : $this->children();
    }
}
