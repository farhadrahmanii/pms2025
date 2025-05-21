<?php

return [

    // Login form
    'login_form' => [

        // Enabled
        'is_enabled' => true

    ],

    // Locales
    'locales' => [

        // Locales list
        'list' => [
            'en' => 'English',

            'fa' => 'Persian',

            'ps' => 'Pashto',

        ],

    ],

    // Projects configuration
    'projects' => [

        // Users affectations
        'affectations' => [

            // Users affectations roles
            'roles' => [

                // Default role
                'default' => 'employee',

                // Role that can manage
                'can_manage' => 'administrator',

                // Roles list
                'list' => [
                    'employee' => 'Employee',
                    'administrator' => 'Administrator'
                ],

                // Roles colors
                'colors' => [
                    'primary' => 'employee',
                    'warning' => 'customer',
                    'danger' => 'administrator'
                ],

            ],

        ],

    ],

    // Tickets configuration
    'tickets' => [

        // Ticket relations types
        'relations' => [

            // Default type
            'default' => 'related_to',

            // Types list
            'list' => [
                'related_to' => 'Related to',
                'blocked_by' => 'Blocked by',
                'duplicate_of' => 'Duplicate of'
            ],

            // Types colors
            'colors' => [
                'related_to' => 'primary',
                'blocked_by' => 'warning',
                'duplicate_of' => 'danger',
            ],

        ],

    ],

    // System constants
    'max_file_size' => 10240,

];
