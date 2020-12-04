#!/bin/bash

docker exec --user 1000 -it app vendor/bin/phpunit $@

