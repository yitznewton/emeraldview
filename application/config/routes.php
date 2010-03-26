<?php

$config['_default']              = 'collection';
$config['ajax/(.+)/browse/(.+)'] = 'ajax/browse/$1/$2';
$config['(.+)/browse/(.+)']      = 'collection/browse/$1/$2';
$config['(.+)/view/(.+)']        = 'collection/view/$1/$2';
$config['(.+)/search']           = 'collection/search/$1';
$config['([^/]+)']               = 'collection/about/$1';
