#!/bin/bash

case "$@" in
  "term" )
    (while true; do
      php entrypoint/terminal.php 2>&1
    done)
  ;;

  "pull" )
    (while true; do
      php entrypoint/update_provider.php 2>&1
    done)
  ;;

esac;
