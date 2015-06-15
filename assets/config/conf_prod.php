<?php

return array(
    'environment'                       => 'prod',
    'template_engine_templates_path'    => '../assets/templates',
    'template_engine_cache_path'        => '../cache/templates',

    'registered_engines'                => array(
        'BingRaw' => array(
            'active'    => true,
            'label'     => 'Bing',
        ),
        'GoogleRaw' => array(
            'active'    => true,
            'label'     => 'Google'
        )
    )
);
