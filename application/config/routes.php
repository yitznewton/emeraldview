<?php

$config['_default']         = 'collection';
$config['(.+)/browse/(.+)'] = 'collection/browse/$1/$2';
$config['(.+)/search']      = 'search/index/$1';
$config['([^/]+)']          = 'collection/about/$1';