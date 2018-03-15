#!/bin/bash
set -e
php /var/www/html/loopServer.php
exec "$@"
