#!/usr/bin/env bash

find ../../../ -name "*.min.min.js" -depth -exec rm -v {} \;
find ../../../ -name ".DS_Store"    -depth -exec rm -v {} \;

mv *.min.js ../build/

cd ../../../kopere_bi/amd/src/

mv *.min.js ../build/

