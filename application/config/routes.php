<?php

$config['_default']         = 'collection';
$config['(.+)/browse/(.+)'] = 'collection/browse/$1/$2';
$config['(.+)/view/(.+)']   = 'collection/view/$1/$2';
$config['(.+)/search']      = 'collection/search/$1';
$config['([^/]+)']          = 'collection/about/$1';
// FIXME: need to account for system-generated 404's