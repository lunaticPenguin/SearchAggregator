<?php

return array(
    'environment'                       => 'dev',
    'template_engine_templates_path'    => '../assets/templates',
    'template_engine_cache_path'        => '../cache/templates',

    'nb_items_displayed'                => 5,

    'registered_engines'                => array(
        'BingRaw' => array(
            'active'    => true,
            'label'     => 'Bing',
        ),
        'GoogleRaw' => array(
            'active'    => true,
            'label'     => 'Google'
        ),
        'YahooRaw' => array(
            'active'    => true,
            'label'     => 'Yahoo!'
        )
    ),

    'cache'                             => 'PixieSession'
);
