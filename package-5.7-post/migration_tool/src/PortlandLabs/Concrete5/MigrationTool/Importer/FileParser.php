<?php

namespace PortlandLabs\Concrete5\MigrationTool\Importer;


use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Attribute;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Block;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute;
use PortlandLabs\Concrete5\MigrationTool\Importer\Attribute\Type\Manager;

class FileParser
{

    protected $simplexml;
    protected $singlePages = array();
    protected $pages = array();
    protected $attributeImporter;

    public function __construct($file)
    {
        $this->simplexml = simplexml_load_file($file);
        $this->attributeImporter = \Core::make('migration/manager/import/attribute');
        $this->blockImporter = \Core::make('migration/manager/import/block');
        $i = 0;
        if ($this->simplexml->pages->page) {
            foreach($this->simplexml->pages->page as $node) {
                $page = $this->parsePage($node);
                $page->setPosition($i);
                $i++;
                $this->pages[] = $page;
            }
        }
        $i = 0;
        if ($this->simplexml->singlepages->page) {
            foreach($this->simplexml->singlepages->page as $node) {
                $page = $this->parsePage($node);
                $page->setPosition($i);
                $i++;
                $this->singlePages[] = $page;
            }
        }
    }

    protected function parsePage($node)
    {
        $page = new Page();
        $page->setName((string) html_entity_decode($node['name']));
        $page->setPublicDate((string) $node['public-date']);
        $page->setOriginalPath((string) $node['path']);
        $page->setFilename((string) $node['filename']);
        $page->setTemplate((string) $node['template']);
        $page->setType((string) $node['pagetype']);
        $page->setUser((string) $node['user']);
        $page->setDescription((string) html_entity_decode($node['description']));

        // Parse attributes
        if ($node->attributes->attributekey) {
            $i = 0;
            foreach($node->attributes->attributekey as $keyNode) {
                $attribute = $this->parseAttribute($keyNode);
                $pageAttribute = new PageAttribute();
                $pageAttribute->setAttribute($attribute);
                $pageAttribute->setPage($page);
                $page->attributes->add($pageAttribute);
                $i++;
            }
        }

        // Parse areas
        if ($node->area) {
            foreach($node->area as $areaNode) {
                $area = $this->parseArea($areaNode);
                $area->setPage($page);
                $page->areas->add($area);
            }
        }

        return $page;
    }

    protected function parseAttribute($node)
    {
        $attribute = new Attribute();
        $attribute->setHandle((string) $node['handle']);
        $value = $this->attributeImporter->driver('unmapped')->parse($node);
        $attribute->setAttributeValue($value);
        return $attribute;
    }

    protected function parseBlock($node)
    {
        $block = new Block();
        $block->setType((string) $node['type']);
        $block->setName((string) $node['name']);
        $value = $this->blockImporter->driver('unmapped')->parse($node);
        $block->setBlockValue($value);
        return $block;
    }


    protected function parseArea($node)
    {
        $area = new Area();
        $area->setName((string) $node['name']);

        // Parse areas
        $nodes = false;
        if ($node->blocks->block) {
            $nodes = $node->blocks->block;
        } else if ($node->block) {
            // 5.6
            $nodes = $node->block;
        }

        if ($nodes) {
            $i = 0;
            foreach($nodes as $blockNode) {
                if ($blockNode['type']) {
                    $block = $this->parseBlock($blockNode);
                    $block->setPosition($i);
                    $block->setArea($area);
                    $area->blocks->add($block);
                    $i++;
                }
            }
        }

        return $area;
    }

    public function getSinglePageEntityObjects()
    {
        return $this->singlePages;
    }

    public function getPageEntityObjects()
    {
        return $this->pages;
    }

}