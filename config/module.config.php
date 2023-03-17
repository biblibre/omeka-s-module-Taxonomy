<?php

namespace Taxonomy;

return [
    'api_adapters' => [
        'invokables' => [
            'taxonomies' => Api\Adapter\TaxonomyAdapter::class,
            'taxonomy_terms' => Api\Adapter\TaxonomyTermAdapter::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Taxonomy\Controller\Admin\Taxonomy' => Controller\Admin\TaxonomyController::class,
            'Taxonomy\Controller\Admin\TaxonomyTerm' => Controller\Admin\TaxonomyTermController::class,
            'Taxonomy\Controller\Site\Taxonomy' => Controller\Site\TaxonomyController::class,
            'Taxonomy\Controller\Site\TaxonomyTerm' => Controller\Site\TaxonomyTermController::class,
        ],
    ],
    'data_types' => [
        'invokables' => [
            'resource:taxonomy' => DataType\Resource\Taxonomy::class,
        ],
        'value_annotating' => [
            'resource:taxonomy',
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
        'resource_discriminator_map' => [
            'Taxonomy\Entity\Taxonomy' => Entity\Taxonomy::class,
            'Taxonomy\Entity\TaxonomyTerm' => Entity\TaxonomyTerm::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Taxonomy\Form\TaxonomyForm' => Service\Form\TaxonomyFormFactory::class,
            'Taxonomy\Form\TaxonomyTermForm' => Service\Form\TaxonomyTermFormFactory::class,
            'Taxonomy\Form\Element\TaxonomySelect' => Service\Form\Element\TaxonomySelectFactory::class,
        ],
    ],
    'navigation' => [
        'AdminResource' => [
            [
                'label' => 'Taxonomies', // @translate
                'class' => 'taxonomies',
                'route' => 'admin/taxonomy',
                'action' => 'browse',
                'resource' => 'Taxonomy\Controller\Admin\Taxonomy',
                'privilege' => 'browse',
                'pages' => [
                    [
                        'route' => 'admin/taxonomy-id',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/taxonomy',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'taxonomy' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Admin',
                                'controller' => 'taxonomy',
                                'action' => 'browse',
                            ],
                        ],
                    ],
                    'taxonomy-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy/:id[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Admin',
                                'controller' => 'taxonomy',
                                'action' => 'show',
                            ],
                        ],
                    ],
                    'taxonomy-term' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy/:taxonomy-id/term[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'taxonomy-id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Admin',
                                'controller' => 'taxonomy-term',
                                'action' => 'browse',
                            ],
                        ],
                    ],
                    'taxonomy-term-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy-term/:id[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                                'taxonomy-id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Admin',
                                'controller' => 'taxonomy-term',
                                'action' => 'show',
                            ],
                        ],
                    ],
                ],
            ],
            'site' => [
                'child_routes' => [
                    'taxonomy-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy/:id[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Site',
                                'controller' => 'taxonomy',
                                'action' => 'show',
                            ],
                        ],
                    ],
                    'taxonomy-term-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/taxonomy-term/:id[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                                'taxonomy-id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Taxonomy\Controller\Site',
                                'controller' => 'taxonomy-term',
                                'action' => 'show',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
