<?php
return array(
    'modules' => array(
        'Stjornvisi',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            '../../../config/test/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
    'factories' => [
        'Stjornvisi\Lib\QueueConnectionFactory' => function () {
            return new \stdClass();
        }
    ]
);
