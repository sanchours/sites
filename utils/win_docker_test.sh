#!/bin/bash

utils/win_docker_copy_data_to_test.sh

echo ""
echo "запускаем все тесты из директории $1"
winpty docker-compose.exe exec -u application app bash -c "cd app && vendor/bin/codecept run $1"

