<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Renderer\ExcelRenderer;

class ViewExcelRendererFactory implements FactoryInterface
{

    /**
     * Create and return the EXCEL view renderer
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ExcelRenderer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $excelRenderer = new ExcelRenderer();

        $resolver = $serviceLocator->get('ViewResolver');
        $excelRenderer->setResolver($resolver);

        $helperManager = $serviceLocator->get('ViewHelperManager');
        $excelRenderer->setHelperPluginManager($helperManager);

        return $excelRenderer;
    }

}
