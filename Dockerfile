# ベースイメージにPHPを指定（Apache付き）
FROM php:8.2-apache

# index.php をApacheの公開フォルダへコピー
COPY . /var/www/html/

# ポート80を開放
EXPOSE 80
