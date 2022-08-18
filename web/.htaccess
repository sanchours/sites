ErrorDocument 404 /404.php

Options +FollowSymLinks
Options -Indexes
rewriteEngine on

# блокируем все url начинающаяся с точкой(.git,.idea)
RedirectMatch 404 /\..*$

RewriteCond %{REQUEST_URI} robots\.txt*
RewriteRule ^(.*)$ index.php [L]

RewriteCond %{REQUEST_URI} /gateway/index\.php*
RewriteRule ^(.*)$ index.php [L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ![.][^\/]*$
RewriteRule ^(.*)$ /index.php

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} (.*)\.php$
RewriteRule ^(.*)$ /index.php


<ifModule mod_expires.c>
    ExpiresActive On
    #по умолчанию кеш в 5 секунд
    ExpiresDefault "access plus 5 seconds"
    # Включаем кэширование изображений и флэш на месяц
    ExpiresByType image/x-icon "access plus 1 month"
    ExpiresByType image/jpeg "access plus 4 weeks"
    ExpiresByType image/png "access plus 30 days"
    ExpiresByType image/gif "access plus 43829 minutes"
    ExpiresByType application/x-shockwave-flash "access plus 2592000 seconds"
    # Включаем кэширование css, javascript и текстовых файлов на одну неделю
    ExpiresByType text/css "access plus 604800 seconds"
    ExpiresByType text/javascript "access plus 604800 seconds"
    ExpiresByType application/javascript "access plus 604800 seconds"
    ExpiresByType application/x-javascript "access plus 604800 seconds"
    # Включаем кэширование html и htm файлов на один день
    ExpiresByType text/html "access plus 43200 seconds"
    # Включаем кэширование xml файлов на десять минут
    ExpiresByType application/xhtml+xml "access plus 600 seconds"
</ifModule>

<ifModule mod_headers.c>
    #кэшировать html и htm файлы на один день
    <FilesMatch "\.(html|htm)$">
        Header set Cache-Control "max-age=43200"
    </FilesMatch>
    #кэшировать css, javascript и текстовые файлы на одну неделю
    <FilesMatch "\.(js|css|txt)$">
        Header set Cache-Control "max-age=604800"
    </FilesMatch>
    #кэшировать флэш и изображения на месяц
    <FilesMatch "\.(flv|swf|ico|gif|jpg|jpeg|png)$">
        Header set Cache-Control "max-age=2592000"
    </FilesMatch>
    #отключить кэширование
    <FilesMatch "\.(pl|php|cgi|spl|scgi|fcgi)$">
        Header set Cache-Control "no-cahe, must-revalidate, no-store"
        Header set Pragma "no-cache"
        Header set Expires "0"
    </FilesMatch>
</ifModule>

# Отдельные правила для robots.txt и sitemap.xml
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} robots.txt
RewriteRule ^(.*)$ /index.php

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} sitemap.xml
RewriteRule ^(.*)$ /index.php
