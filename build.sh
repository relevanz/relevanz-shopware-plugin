#!/usr/bin/env bash

#clean folder
rm -rf Backend RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=./Backend/ --exclude=README.md --exclude=RelevaRetargeting.zip --exclude=.directory --exclude=build.sh ./ ./Backend/

#install
composer install --no-dev -n -o -d Backend/Relevanz
if [ -d "Backend/Relevanz/vendor" ]; then
    #zip + clean
    zip -r RelevaRetargeting.zip Backend
fi
rm -rf Backend
