<?php

namespace unit\components\auth\model;

use skewer\build\Page\Auth\PageUsers;

class UsersTest extends \Codeception\Test\Unit
{
    /* @var PageUsers */
    protected $oUsers;
    protected $errSave = 'Проблема с добавлением пользователя';
    protected $errValid = 'Проблема с валидацией';

    /**
     * данные для теста.
     */
    public function providerPower()
    {
        return [
            ['test@test888.com', '123456', true],
            ['good1111@email.ru', '134679123', true],
            ['bedssss', '123456', false],
            ['www@sss', 'qweeewwwww676', false],
            ['bedssss@test4.tt', '123', false],
        ];
    }

    protected function setUp()
    {
        $this->oUsers = new PageUsers();
    }

    protected function tearDown()
    {
        $this->oUsers = null;
    }

    /**
     * @dataProvider providerPower
     * @covers \skewer\build\Page\Auth\PageUsers::validate()
     *
     * @param mixed $login
     * @param mixed $pass
     * @param mixed $res
     */
    public function testUsers($login, $pass, $res)
    {
        $this->oUsers->login = $login;
        $this->oUsers->pass = $pass;
        $this->assertEquals($res, $this->oUsers->validate(), $this->errValid);
        if ($res == true && $res == $this->oUsers->validate()) {
            $this->assertTrue($this->oUsers->save(), $this->errSave);
        }
    }

    /**
     * @group save
     * @covers \skewer\build\Page\Auth\PageUsers::save()
     */
    public function testSave()
    {
        $login = 'test257@save.yy';
        $pass = '13469798';
        $this->oUsers->login = $login;
        $this->oUsers->pass = $pass;
        $this->assertTrue($this->oUsers->validate(), $this->errValid);
        $this->assertTrue($this->oUsers->save(), $this->errSave);

        //Повторное сохранение
        $oUsersNew = new PageUsers();
        $oUsersNew->login = $login;
        $oUsersNew->pass = $pass;
        $this->assertFalse($oUsersNew->save(), $this->errSave);
    }
}
