<?php

namespace unit\helpers;

use skewer\helpers\Mailer;
use unit\data\TestHelper;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 17.08.2015
 * Time: 10:36.
 */
class MailerTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\helpers\Mailer::convertEmail
     */
    public function testEmail()
    {
        $this->assertSame(
            'qwe@qwe.ew',
            TestHelper::callPrivateMethod(Mailer::getInstance(), 'convertEmail', ['qwe@qwe.ew']),
            'не работает на незаменяемых символах'
        );

        $this->assertSame(
            'xn--80a1acny@xn--d1acufc.xn--p1ai',
            TestHelper::callPrivateMethod(Mailer::getInstance(), 'convertEmail', ['почта@домен.рф']),
            'не работает замена для email'
        );

        $this->assertSame(
            'xn--80a1acny@xn--d1acufc.xn--p1ai,qwe@qwe.qw,' .
            'xn--80a1acny@xn--d1acufc.xn--p1ai',
            TestHelper::callPrivateMethod(
                Mailer::getInstance(),
                'convertEmail',
                ['почта@домен.рф, qwe@qwe.qw, почта@домен.рф']
            ),
            'не работает замена по списку'
        );
    }
}
