<?php

namespace skewer\components\regions;

use skewer\components\config\Exception;
use skewer\components\regions\models\Regions;
use yii\db\ActiveRecord;

class SubDomain
{
    const UTM_LABEL = 'utm_geo';

    /**
     * Ищет регион по местоположению и возвращает его поддомен.
     *
     * @throws Exception
     *
     * @return null|ActiveRecord|string
     */
    public static function findSubDomain()
    {
        // поиск целевого домена по utm метке
        $subDomain = self::getDomainByUtm();

        // поиск целевого домена по IP
        if (!$subDomain) {
            $subDomain = self::getDomainByIp();
        }

        // установка дефолтного домена
        if (!$subDomain) {
            $region = Regions::getDefaultRegion();
            if ($region instanceof Regions) {
                $subDomain = $region->domain;
            }
        }

        return $subDomain;
    }

    /**
     * Поиск домена по UTM-метке.
     *
     * @return string
     */
    private static function getDomainByUtm()
    {
        $utmLabel = \Yii::$app->getRequest()->get(
            self::UTM_LABEL,
            ''
        );
        if ($utmLabel) {
            $region = Regions::getActiveRegionByUtm($utmLabel);
            if ($region) {
                return $region->domain;
            }
        }
    }

    /**
     * Поиск домена по ip (ipgeobase).
     *
     * @return string
     */
    private static function getDomainByIp()
    {
        $ipUser = \Yii::$app->request->getUserIP();

        if ($ipUser) {
            $urlGeoBase = 'http://ipgeobase.ru:7020/geo?ip=' . $ipUser;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $urlGeoBase);
            //Возврат результата передачи в качестве строки из curl_exec() вместо прямого вывода в браузер
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //Макс. время выполнения
            curl_setopt($curl, CURLOPT_TIMEOUT, 3);

            //Ответ
            $response = curl_exec($curl);
            //HTTP Код ошибки
            $codeHttp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($codeHttp == 200) {
                $result = new \SimpleXMLElement($response);

                if (isset($result->ip->city, $result->ip->region, $result->ip->district)) {
                    $checkConditions = [
                        ['city' => (string) ($result->ip->city)],
                        ['region' => (string) ($result->ip->region)],
                        ['fed_district' => (string) ($result->ip->district)],
                    ];

                    foreach ($checkConditions as $condition) {
                        $domain = Regions::getActiveDomainByCondition($condition);
                        if ($domain) {
                            return $domain;
                        }
                    }
                }
            }
        }

        return '';
    }
}
