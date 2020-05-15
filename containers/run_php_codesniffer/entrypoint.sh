#!/bin/sh -l

set -e
# set -x
# pwd
# ls -l

echo '---Installing dependencies---'
php ./composer.phar install

sh runCodeSniffer.sh

echo "---fin---"
