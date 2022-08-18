#!/bin/bash

echo ""
echo "очищаем тестовую базу"
winpty docker-compose.exe exec mysql bash -c "for i in \`mysql -u root -pdev -e 'show tables' testbase\`; do mysql -u root -pdev -e \"use testbase; drop table \\\`\$i\\\`\" ; done"
winpty docker-compose.exe exec mysql bash -c "for i in \`mysql -u root -pdev -e 'show tables' testbase\`; do mysql -u root -pdev -e \"use testbase; drop table \\\`\$i\\\`\" ; done"

echo ""
echo "копируем данные в тестовую базу"
winpty docker-compose.exe exec mysql bash -c "mysqldump -u root -pdev database | mysql -u root -pdev testbase"
