<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Type;

use Concrete\Core\Page\Type\Type;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Item\Item;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Item\ItemInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\MapperInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\ContentMapper\TargetItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\ContentMapper\TargetItemInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\PageTypePublishTarget;

defined('C5_EXECUTE') or die("Access Denied.");

class PageType implements MapperInterface
{

    public function getMappedItemPluralName()
    {
        return t('Page Types');
    }

    public function getHandle()
    {
        return 'page_type';
    }

    public function getItems(Batch $batch)
    {
        $types = array();
        foreach($batch->getPages() as $page) {
            if ($page->getType() && !in_array($page->getType(), $types)) {
                $types[] = $page->getType();
            }
        }

        foreach($batch->getObjectCollection('page_type')->getTypes() as $pageType) {
            if (!in_array($pageType->getHandle(), $types)) {
                $types[] = $pageType->getHandle();
            }
            $target = $pageType->getPublishTarget();
            if ($target instanceof PageTypePublishTarget) {
                if (!in_array($target->getPageType(), $types)) {
                    $types[] = $target->getPageType();
                }
            }
        }

        $items = array();
        foreach($types as $type) {
            $item = new Item();
            $item->setIdentifier($type);
            $items[] = $item;
        }
        return $items;
    }

    public function getMatchedTargetItem(Batch $batch, ItemInterface $item)
    {
        $type = Type::getByHandle($item->getIdentifier());
        if (is_object($type)) {
            $targetItem = new TargetItem($this);
            $targetItem->setItemId($type->getPageTypeHandle());
            $targetItem->setItemName($type->getPageTypeDisplayName());
            return $targetItem;
        } else {
            $collection = $batch->getObjectCollection('page_type');
            foreach($collection->getTypes() as $type) {
                if ($type->getHandle() == $item->getIdentifier()) {
                    $targetItem = new TargetItem($this);
                    $targetItem->setItemId($type->getHandle());
                    $targetItem->setItemName($type->getHandle());
                    return $targetItem;
                }
            }

        }
    }

    public function getBatchTargetItems(Batch $batch)
    {
        $collection = $batch->getObjectCollection('page_type');
        $items = array();
        foreach($collection->getTypes() as $type) {
            if (!$type->getPublisherValidator()->skipItem()) {
                $item = new TargetItem($this);
                $item->setItemId($type->getHandle());
                $item->setItemName($type->getName());
                $items[] = $item;
            }
        }
        return $items;
    }


    public function getInstalledTargetItems(Batch $batch)
    {
        $types = Type::getList();
        usort($types, function($a, $b) {
            return strcasecmp($a->getPageTypeName(), $b->getPageTypeName());
        });
        $items = array();
        foreach($types as $type) {
            $item = new TargetItem($this);
            $item->setItemId($type->getPageTypeHandle());
            $item->setItemName($type->getPageTypeDisplayName());
            $items[] = $item;
        }
        return $items;
    }

    public function getTargetItemContentObject(TargetItemInterface $targetItem)
    {
        return Type::getByHandle($targetItem->getItemID());
    }




}