<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración Dinámica de Permisos
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los recursos y las acciones disponibles.
    | Los permisos se generan automáticamente con el formato: recurso.accion
    |
    */

    'resources' => [
        'clientes' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Clientes',
        ],
        'proyectos' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Proyectos',
        ],
        'programas' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Programas',
        ],
        'fases' => [
            'actions' => ['ver', 'editar'],
            'label' => 'Fases',
        ],
        'dashboards' => [
            'actions' => ['ver'],
            'label' => 'Dashboards',
        ],
        'abastecimiento' => [
            'actions' => ['ver', 'crear', 'editar'],
            'label' => 'Abastecimiento',
        ],
        'users' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Usuarios',
        ],
        'roles' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Roles',
        ],
        'permissions' => [
            'actions' => ['ver', 'crear', 'editar', 'eliminar'],
            'label' => 'Permisos',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de Acciones a Métodos de Policy
    |--------------------------------------------------------------------------
    |
    | Define qué acción se mapea a qué método de Policy
    |
    */

    'action_mapping' => [
        'ver' => ['viewAny', 'view'],
        'crear' => ['create'],
        'editar' => ['update'],
        'eliminar' => ['delete', 'forceDelete'],
        'restaurar' => ['restore'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles del Sistema
    |--------------------------------------------------------------------------
    |
    | Define los roles disponibles y sus permisos por defecto
    |
    */

    'roles' => [
        'Administrador' => [
            'permissions' => '*', // Todos los permisos
            'label' => 'Administrador del Sistema',
        ],
        'Ingenieria' => [
            'permissions' => [
                'clientes.ver',
                'clientes.crear',
                'clientes.editar',
                'proyectos.ver',
                'proyectos.crear',
                'proyectos.editar',
                'proyectos.eliminar',
                'programas.ver',
            ],
            'label' => 'Ingeniería',
        ],
        'Captura' => [
            'permissions' => [
                'programas.ver',
                'programas.crear',
                'programas.editar',
            ],
            'label' => 'Captura de Datos',
        ],
        'Abastecimiento' => [
            'permissions' => [
                'programas.ver',
                'abastecimiento.ver',
                'abastecimiento.crear',
                'abastecimiento.editar',
                'dashboards.ver',
                'fases.ver',
            ],
            'label' => 'Abastecimiento',
        ],
        'Corte' => [
            'permissions' => [
                'dashboards.ver',
                'fases.ver',
            ],
            'label' => 'Corte',
        ],
        'Ensamblado' => [
            'permissions' => [
                'dashboards.ver',
                'fases.ver',
            ],
            'label' => 'Ensamblado',
        ],
        'Instalacion' => [
            'permissions' => [
                'dashboards.ver',
                'fases.ver',
            ],
            'label' => 'Instalación',
        ],
        'Finalizado' => [
            'permissions' => [
                'dashboards.ver',
                'fases.ver',
            ],
            'label' => 'Finalizado',
        ],
    ],
];
