#!/bin/sh
phpunit --colors --bootstrap=$(dirname $0)/unit/bootstrap.php $(dirname $0)/unit/

