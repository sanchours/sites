#!/bin/bash

utils/docker_copy_data_to_test.sh

echo ""
echo "запускаем все тесты"
docker-compose exec -u application app bash -c "cd app && vendor/bin/codecept run tests/codeception/unit/"

