#!/bin/bash

echo ""
echo "очищаем тестовую базу"
docker-compose exec mysql bash -c "for i in \`mysql -u root -pdev -e 'show tables' testbase\`; do mysql -u root -pdev -e \"use testbase; drop table \\\`\$i\\\`\" ; done"
docker-compose exec mysql bash -c "for i in \`mysql -u root -pdev -e 'show tables' testbase\`; do mysql -u root -pdev -e \"use testbase; drop table \\\`\$i\\\`\" ; done"

echo ""
echo "копируем данные в тестовую базу"
docker-compose exec mysql bash -c "mysqldump -u root -pdev database | mysql -u root -pdev testbase"
