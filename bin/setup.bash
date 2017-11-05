#!/usr/bin/env bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
APP_DIR="$(readlink -e $SCRIPT_DIR/./../../../../app)"
APP_VIEWS_DIR="$APP_DIR/Resources/PimEnrichBundle/views"
echo $APP_DIR
mkdir -p $APP_VIEWS_DIR/MassEditAction/product/configure
cp ./../src/Resources/views/icecat-enrichment.html.twig $APP_VIEWS_DIR/MassEditAction/product/configure

cd $APP_DIR
./console akeneo:batch:create-job "Akeneo Mass Edit Connector" "mass_edit_icecat_enrichment" "mass_edit" "mass_edit_icecat_enrichment"
