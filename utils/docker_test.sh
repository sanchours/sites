#!/bin/bash

utils/docker_copy_data_to_test.sh

echo ""
echo "запускаем все тесты из директории $1"
docker-compose exec -u application app bash -c "cd app && vendor/bin/codecept run $1"

