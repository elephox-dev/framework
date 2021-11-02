#!/usr/bin/env bash

##############################################################################################################################
# courtesy of Taylor Otwell: https://github.com/laravel/framework/blob/c0b7719c52c653ff50fe5a965661acd4b7aa3ea5/bin/split.sh #
##############################################################################################################################

set -e
set -x

CURRENT_BRANCH="main"

function split()
{
    SHA1=$(./bin/splitsh-lite --prefix="$1")
    git push "$2" "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add "$1" "$2" || true
}

git pull origin $CURRENT_BRANCH

remote collection git@github.com:philly-framework/collection.git
remote http git@github.com:philly-framework/http.git
remote support git@github.com:philly-framework/support.git

split 'src/Collection' collection
split 'src/Http' http
split 'src/Support' support
