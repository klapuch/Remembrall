#!/bin/sh
TMPFILE=tmp.$$
INDEX=$PWD/www/index.php
MAINTENANCE=$PWD/www/.maintenance.php
mv $MAINTENANCE $TMPFILE
mv $INDEX $MAINTENANCE
mv $TMPFILE $INDEX
