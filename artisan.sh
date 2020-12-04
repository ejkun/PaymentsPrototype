#!/bin/bash

docker exec --user 1000 -it app php artisan $@
