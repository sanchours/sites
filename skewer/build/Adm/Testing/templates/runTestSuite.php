#!/bin/bash
echo -en "\033[37;1;41m Вы запустили тест <?= $title; ?> \033[0m \n"
cd <?= $pathToSite . "\n"; ?>
if [[ ! -f /tmp/.X10-lock ]]; then
Xvfb :10 -ac &
else
echo "INFO: $(date) - X Server already running" 1>&2
fi
export DISPLAY=:10
export PATH="/home/canape/bin:/home/canape/.local/bin:/opt/src/katalon:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:/usr/lib/jvm/java-8-oracle/bin:/usr/lib/jvm/java-8-oracle/db/bin:/usr/lib/jvm/java-8-oracle/jre/bin"
xvfb-run -a /opt/src/katalon/./katalon -noSplash  -runMode=console -projectPath="<?= $pathToSite; ?>tests/acceptanceKS/acceptanceKS.prj" -retry=0 -testSuitePath="<?= $pathTestSuite; ?>" -executionProfile="default" -browserType="Chrome"
