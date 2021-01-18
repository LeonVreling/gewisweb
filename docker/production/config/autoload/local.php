<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */

return [
    /**
     * Dreamspark credentials.
     */
    'dreamspark' => [
        'account' => '',
        'key' => ''
    ],

    /**
     * Email configuration.
     */
    'email' => [
        'transport' => 'File',
        'options' => [
            'path' => 'data/mail/'
        ],
        'from' => 'web@gewis.nl',
        'to' => [
            'activity_creation' => 'secr@gewis.nl',
            'activity_calendar' => 'cib@gewis.nl',
            'poll_creation' => '',
            'organ_update' => ''
        ]
    ],
    'cookie_domain' => '*.gewis.nl',

    /*
     * API key for google calendar
     */
    'calendar' => [
        'google_api_key' => '',
        'google_calendar_key' => ''
    ],
	/*
     * Path to folder in local filesystem available for browsing
     */
    'filebrowser_folder' => getcwd() . '/public/webfiles/',

    'glide' => [
        'base_url' => '',
        'signing_key' => ''
    ]
];
