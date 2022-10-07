#!/bin/bash
(while true; do
    php entrypoint/update_provider.php 2>&1
done)