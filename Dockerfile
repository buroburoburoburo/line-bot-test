# PHP + Apache イメージ
FROM php:8.2-apache

# 拡張を入れたい場合（例：curl）
RUN docker-php-ext-install pdo pdo_mysql

# ソースコードを Apache の公開ディレクトリへコピー
COPY . /var/www/html/

# Apache の設定を許可
RUN chown -R www-data:www-data /var/www/html

# Render に「サーバーが起動中」と認識させるために
CMD ["apache2-foreground"]

EXPOSE 80
