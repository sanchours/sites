<?php

/* @var $scenario Codeception\Scenario */

$I = new AcceptanceTester($scenario);
$I->amOnPage('/');
$I->see('Главная');
$I->see('Главная', 'title');

$I->amOnPage('/not_found/');
$I->see('404', 'h1');

$I->amOnPage('/ahsfjkasasf/');
$I->see('404', 'h1');
