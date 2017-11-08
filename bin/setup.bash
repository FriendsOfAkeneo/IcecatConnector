#!/usr/bin/env bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BIN_DIR="$(readlink -e $SCRIPT_DIR/./../../../../bin)"

cd $BIN_DIR
echo $(pwd)
./console akeneo:batch:create-job "Akeneo Mass Edit Connector" "mass_edit_icecat_enrichment" "mass_edit" "mass_edit_icecat_enrichment"
