#!/bin/bash
#
# Ivan Scherbak <dev@funivan.com> 2013
# Find all behat test configs and run test
# Behat config files like parser-behat.yml (*-behat.yml)
# Put script in sub directory of your project
#
# All params goes into behat
# ./run.sh -h
# ./run.sh --format=progress

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

BEHAT_PATH="$SCRIPT_DIR/../bin/behat";

$BEHAT_PATH --config=behat.yml $@