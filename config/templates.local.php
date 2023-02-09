<?php

return [
    'templates' => [
        /**
         * The template file extension; it defaults to "html.twig".
         */
        'extension' => 'html.twig',

        /**
         * Namespace / path pairs
         *
         * Numeric namespaces imply the default/main namespace. Paths may be
         * strings or arrays of string paths to associate with the namespace.
         */
        'paths' => [
            'app'    => [__DIR__ . '/../resources/templates/app'],
        ],
    ],
    'twig' => [
        /**
         * Twig's auto-escaping strategy
         *
         * It can be set to one of "html", "js", "css", "url", or "false".
         */
        'autoescape' => 'html',

        /**
         * The path to the cached templates
         */
        'cache_dir' => __DIR__ . '/../var/cache/',

        /**
         * The base URL for assets
         */
        //'assets_url' => '',

        /**
         * The base version for assets
         */
        //'assets_version' => '',

        /**
         * Extension service names or instances
         */
        //'extensions' => [],

        /**
         * Global variables passed to Twig templates
         */
        //'globals' => [],

        /**
         * Enable or disable optimisations
         *
         * -1: Enable all optimisations (default).
         * 0: Disable optimizations
         */
        //'optimizations' => -1,

        /**
         * Runtime loader names or instances
         */
        'runtime_loaders' => [
            //
        ],

        /**
         * The default timezone identifier, e.g. America/New_York.
         */
        'timezone' => 'Europe/Berlin',

        /**
         * Whether to recompile the template whenever the source code changes.
         */
        'auto_reload' => true,
    ],
];