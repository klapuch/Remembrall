#!/bin/sh
PROJECTS="/var/www/html"
TARGET="$PROJECTS/Remembrall"
rsync -rtilpzcvh \
    --rsync-path='sudo rsync' \
    --exclude-from="$TRAVIS_BUILD_DIR/.rsync_ignore" \
    --delete \
    $TRAVIS_BUILD_DIR root@81.95.108.74:"$PROJECTS && $TARGET/vendor/bin/phing -f $TARGET/build.xml after-deploy"
