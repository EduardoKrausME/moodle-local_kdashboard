#!/usr/bin/env bash


rm -rf ../../../theme/boost_magnific/_editor/css/*
rm -rf ../../../theme/boost_magnific/_editor/fonts/*
rm -rf ../../../theme/boost_magnific/_editor/js/*
rm -rf ../../../theme/boost_magnific/_editor/libs/*
rm -rf ../../../theme/boost_magnific/_editor/resources/*


cp -r css/*       ../../../theme/boost_magnific/_editor/css/
cp -r fonts/*     ../../../theme/boost_magnific/_editor/fonts/
cp -r js/*        ../../../theme/boost_magnific/_editor/js/
cp -r libs/*      ../../../theme/boost_magnific/_editor/libs/
cp -r resources/* ../../../theme/boost_magnific/_editor/resources/

cp -r ../templates/vvveb/* ../../../theme/boost_magnific/templates/vvveb/
