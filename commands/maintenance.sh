#!/bin/sh
TMPFILE=tmp.$$
INDEX=$PWD/www/index.php
MAINTENANCE=$PWD/www/.maintenance.php
mv $INDEX $TMPFILE
mv $MAINTENANCE $INDEX
mv $TMPFILE $MAINTENANCE
