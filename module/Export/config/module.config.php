<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Export\Controller\Index' => 'Export\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /export/:controller/:action
            'export' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/export/:action/:id/:filename',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Export\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
        ),
    ),
);
