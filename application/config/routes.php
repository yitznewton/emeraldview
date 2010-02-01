<?php

$config['_default']         = 'collection';
$config['(.+)/browse/(.+)'] = 'collection/browse/$1/$2';
$config['(.+)/view/(.+)']   = 'collection/view/$1/$2';
$config['(.+)/search']      = 'collection/search/$1';
$config['([^/]+)']          = 'collection/about/$1';
// TODO this just "forwards" without redirecting.  can we rewrite this
// behavior to redirect?
// $config['(.+)/.*']          = 'collection';  // catch-all for everything else
