#!/usr/bin/env bash

# This script build all assets for production environment

# Find out directory of this script, no matter from where we call it, even via symlinks
SOURCE="${BASH_SOURCE[0]}"
DIR="$( dirname "$SOURCE" )"
while [ -h "$SOURCE" ]
do
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
  DIR="$( cd -P "$( dirname "$SOURCE"  )" && pwd )"
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

cd $DIR/..

echo "Updating git submodules..."
git submodule update --init --recursive --force

echo "Updating database..."
./vendor/bin/doctrine-module migrations:migrate --no-interaction

echo "Compiling CSS..."
compass compile -s compressed --force

echo "Compiling JavaScript..."
cd htdocs/js
mkdir -p min
for file in *.js ; do

    # Discard warnings for third party code
    if [[ $file =~ jquery|bootstrap ]]
    then
        thirdparty="--third_party --warning_level QUIET"
    else
        thirdparty=""
    fi

    echo "$file"
    ngmin "$file" >(uglifyjs - -o "min/$file")
done
ngmin "../lib/select2/select2.js" >(uglifyjs - -o "min/select2.js")
sleep 2

echo "Concatenate JavaScript..."
cd min/

# CAUTION: This must be the exact same files in reverse order than in module/Application/view/layout/layout.phtml
cat \
../../lib/jquery/jquery-1.9.1.min.js \
select2.js \
../../lib/angular/angular.min.js \
../../lib/angular/angular-resource.min.js \
../../lib/angular-ui/build/angular-ui.min.js \
../../lib/ui-bootstrap/ui-bootstrap-tpls-0.2.0.min.js \
../../lib/ng-grid/build/ng-grid.min.js \
app.js \
services.js \
controllers.js \
filters.js \
directives.js \
> application.js
