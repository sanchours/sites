#!/bin/bash

echo ""
echo "стираем все привелегии"
docker-compose exec mysql bash -c "mysql -pdev -e \"REVOKE ALL PRIVILEGES ON \"testbase\".* FROM 'dev'@'%'\""

echo ""
echo "удаляем базу"
docker-compose exec mysql bash -c "mysqladmin -pdev drop testbase -f"

echo ""
echo "создаем базу заново"
docker-compose exec mysql bash -c "mysqladmin -u root -pdev create testbase"

echo ""
echo "выдаем привелегии"
docker-compose exec mysql bash -c "mysql -pdev -e \"GRANT ALL PRIVILEGES ON \"testbase\".* TO 'dev'@'%';\""
