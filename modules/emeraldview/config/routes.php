<?php

$config['_default']              = 'emeraldview';
$config['ajax/(.+)/browse/(.+)'] = 'ajax/browse/$1/$2';
$config['(.+)/browse/(.+)']      = 'emeraldview/browse/$1/$2';
$config['(.+)/view/(.+)']        = 'emeraldview/view/$1/$2';
$config['(.+)/search']           = 'emeraldview/search/$1';
$config['([^/]+)']               = 'emeraldview/about/$1';
