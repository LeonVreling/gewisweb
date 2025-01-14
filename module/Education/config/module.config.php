<?php

namespace Education;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Education\Controller\{
    AdminController,
    EducationController,
};
use Education\Controller\Factory\{
    AdminControllerFactory,
    EducationControllerFactory,
};
use Laminas\Router\Http\{
    Literal,
    Segment,
};

return [
    'router' => [
        'routes' => [
            'education' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/education',
                    'defaults' => [
                        'controller' => EducationController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'course' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/course[/:code]',
                            'constraints' => [
                                'code' => '[a-zA-Z0-9]{5,6}',
                            ],
                            'defaults' => [
                                'action' => 'course',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'download' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/download/:id',
                                    'constraints' => [
                                        'id' => '[0-9]*',
                                    ],
                                    'defaults' => [
                                        'action' => 'download',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'default' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                        ],
                    ],
                ],
                'priority' => 100,
            ],
            'admin_education' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin/education',
                    'defaults' => [
                        'controller' => AdminController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                        ],
                    ],
                    'add_course' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/add/course',
                            'defaults' => [
                                'action' => 'addCourse',
                            ],
                        ],
                    ],
                    'bulk_upload_exam' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/bulk/exam',
                            'defaults' => [
                                'action' => 'bulkExam',
                            ],
                        ],
                    ],
                    'bulk_upload_summary' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/bulk/summary',
                            'defaults' => [
                                'action' => 'bulkSummary',
                            ],
                        ],
                    ],
                    'bulk_edit_exam' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/edit/exam',
                            'defaults' => [
                                'action' => 'editExam',
                            ],
                        ],
                    ],
                    'bulk_edit_summary' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/edit/summary',
                            'defaults' => [
                                'action' => 'editSummary',
                            ],
                        ],
                    ],
                    'delete_temp' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:type/:filename/delete',
                            'constraints' => [
                                'type' => 'exam|summary',
                            ],
                            'defaults' => [
                                'action' => 'deleteTemp',
                            ],
                        ],
                    ],
                ],
                'priority' => 100,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            AdminController::class => AdminControllerFactory::class,
            EducationController::class => EducationControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'education' => __DIR__ . '/../view/',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Model/',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Model' => __NAMESPACE__ . '_driver',
                ],
            ],
        ],
    ],
];
