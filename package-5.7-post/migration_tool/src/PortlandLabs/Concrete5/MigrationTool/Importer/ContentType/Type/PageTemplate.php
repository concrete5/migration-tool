<?php

namespace PortlandLabs\Concrete5\MigrationTool\Importer\ContentType\Type;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Attribute;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Block;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageTemplate as CorePageTemplate;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageTemplateObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Importer\ContentType\TypeInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class PageTemplate implements TypeInterface
{

    protected $simplexml;

    public function getObjectCollection(\SimpleXMLElement $element)
    {
        $this->simplexml = $element;
        $collection = new PageTemplateObjectCollection();
        if ($this->simplexml->pagetemplates->pagetemplate) {
            foreach($this->simplexml->pagetemplates->pagetemplate as $node) {
                $template = new CorePageTemplate();
                $template->setHandle((string) $node['handle']);
                $template->setIcon((string) $node['icon']);
                $template->setName((string) $node['name']);
                $collection->getTemplates()->add($template);
                $template->setCollection($collection);
            }
        }
        return $collection;
    }

}
