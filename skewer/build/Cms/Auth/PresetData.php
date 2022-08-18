<?php

$aLanguage = [];

$aLanguage['ru']['mail_title_new_pass'] = 'Ваш пароль изменен';
$aLanguage['ru']['mail_new_pass'] = '<p>Уважаемый пользователь!</p>
<p>Ваш пароль на сайте [ссылка сайта] был успешно изменен.</p>';

$aLanguage['ru']['mail_title_reset_password'] = 'Сброс пароля учетной записи';
$aLanguage['ru']['mail_reset_password'] = '<p>Уважаемый пользователь!</p>

<p>Чтобы сбросить пароль, пройдите по этой <a href="[link]">ссылке</a></p>

<p>__</p>

<p>С уважением, администрация сайта [ссылка сайта]</p>';

/*** EN ***/

$aLanguage['en']['mail_title_reset_password'] = 'Reset account password';
$aLanguage['en']['mail_reset_password'] = '<p> Dear user! </p>

<p> To reset your password, follow this <a href="[link]">link</a> </p>

<p> __ </p>

<p> Sincerely, Administration of the site [site link] </p> ';

$aLanguage['en']['mail_title_new_pass'] = 'Your password has been changed';
$aLanguage['en']['mail_new_pass'] = '<p> Dear user! </p>
<p> Your password [site link] was changed successfully</a> </p>';

return $aLanguage;
