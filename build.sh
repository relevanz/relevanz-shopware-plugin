#!/usr/bin/env bash

#clean folder
rm -rf Backend RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=./Backend/ --exclude=RelevaRetargeting.zip --exclude=.directory --exclude=build.sh ./ ./Backend/

#remove shopware-requieres
#sed -i '/"shopware\/core": "/d' ./Backend/composer.json
#sed -i '/"shopware\/administration": "/d' ./Backend/composer.json
#sed -i '/"shopware\/storefront": "/d' ./Backend/composer.json

#install
#composer install --no-dev -n -o -d Backend

#rollback remove requieres
#cp composer.json Backend

#zip + clean
zip -r RelevaRetargeting.zip Backend
rm -rf Backend
