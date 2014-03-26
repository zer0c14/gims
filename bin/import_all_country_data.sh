#!/usr/bin/env bash

echo 'Started. See data/logs/import_all_country.log for progress...'
> data/logs/all.log
time find -L data/cache/country_data -type f -name '*_13.xlsm' ! -name '~$*' -print -exec date \; -exec php htdocs/index.php import jmp {} \; &> data/logs/import_all_country.log
