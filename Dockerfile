# ベースイメージ（Apache付きのPHP）
FROM php:8.2-apache

# 必要な拡張をインストール（LINE Bot SDKなどに必要なことが多い）
RUN docker-php-ext-install pdo pdo_mysql

# ソースコードをコピー
COPY . /var/www/html/

# Apacheユーザー権限設定
RUN chown -R www-data:www-data /var/www/html

# Renderに「起動完了」を知らせるコマンド
CMD ["apache2-foreground"]

# ポート80を開放
EXPOSE 80
