#!/usr/bin/env bash
set -euo pipefail

# Сборка релизного архива Rotor (зеркалит .github/workflows/create-release.yml)
# Использование: ./build-release.sh <версия>
# Пример:        ./build-release.sh 13.1

VERSION="${1:?Укажи версию: ./build-release.sh 13.1}"

# Корень репозитория (каталог скрипта) и URL origin — без хардкода путей
repoRoot="$(cd "$(dirname "$0")" && pwd)"
repoUrl="$(git -C "$repoRoot" remote get-url origin)"

# Сборка во временном каталоге, удаляется при выходе
buildDir="$(mktemp -d)"
trap 'rm -rf "$buildDir"' EXIT

dist="$repoRoot/dist"
mkdir -p "$dist"
ZIP="$dist/rotor$VERSION.zip"

# Свежий клон
git clone "$repoUrl" "$buildDir"
cd "$buildDir"

# .env
mv .env.example .env

# Composer
composer install --no-dev -o --prefer-dist

# Node
npm ci
npm run build

# Чистка vendor
php vendor/bin/cleanup
find vendor/nesbot/carbon/src/Carbon/Lang -maxdepth 1 -name '*.php' \
  ! -name 'en.php' ! -name 'ru.php' -delete

# Удаляем лишнее из каталога (тот же набор, что workflow исключает из архива)
rm -rf \
  .git \
  .github \
  node_modules \
  tests \
  package.json \
  package-lock.json \
  vite.config.js \
  pint.json \
  phpstan.neon \
  phpunit.xml \
  composer.lock \
  .editorconfig \
  .gitignore \
  .gitattributes \
  deploy.php \
  docker-compose.yml \
  build-release.sh \
  build-upgrade.sh

# Артефакты сборки
rm -f storage/logs/*.log
find "$buildDir" -name '.DS_Store' -delete

# Архив (всё как есть в каталоге)
rm -f "$ZIP"
7zz a -tzip -mx=9 "$ZIP" . -xr!'.DS_Store'

echo "Готово: $ZIP"
echo "--- содержимое архива ---"
7zz l "$ZIP"
