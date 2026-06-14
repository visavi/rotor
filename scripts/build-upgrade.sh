#!/usr/bin/env bash
set -euo pipefail

# Сборка пакета обновления: файлы, изменившиеся между двумя версиями,
# пакуются в dist/rotor<версия2>_upgrade.zip с сохранением структуры.
# Использование: ./scripts/build-upgrade.sh <версия1> <версия2>   (без префикса v)
# Пример:        ./scripts/build-upgrade.sh 13.0.0 13.1.0

if [ "$#" -lt 2 ]; then
    echo "Usage (без v): ./scripts/build-upgrade.sh 13.0.0 13.1.0"
    exit 1
fi

ver1="$1"
ver2="$2"
tag1="v$ver1"
tag2="v$ver2"
repoRoot="$(cd "$(dirname "$0")/.." && pwd)"

dist="$repoRoot/dist"
mkdir -p "$dist"
ZIP="$dist/rotor${ver2}_upgrade.zip"

# Промежуточный каталог, удаляется при выходе
stageDir="$(mktemp -d)"
trap 'rm -rf "$stageDir"' EXIT

git -C "$repoRoot" pull || true

if ! files=$(git -C "$repoRoot" diff "$tag1" "$tag2" --name-only); then
    echo "Error: git diff failed. Check that tags exist."
    exit 1
fi

while IFS= read -r file; do
    [ -n "$file" ] || continue
    src="$repoRoot/$file"
    [ -f "$src" ] || continue

    dest="$stageDir/$file"
    mkdir -p "$(dirname "$dest")"
    echo "copy: $file"
    cp "$src" "$dest"
done <<< "$files"

# Удаляем лишнее из staging (тот же набор, что в build-release.sh)
( cd "$stageDir" && rm -rf \
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
    scripts )

# public/build из текущего проекта (целиком — vite хеширует имена + manifest.json)
buildSrc="$repoRoot/public/build"
if [ -d "$buildSrc" ]; then
    echo "copy: public/build"
    mkdir -p "$stageDir/public"
    cp -R "$buildSrc" "$stageDir/public/build"
else
    echo "warning: $buildSrc не найден — собери фронт (npm run build)" >&2
fi

# Архив
rm -f "$ZIP"
( cd "$stageDir" && 7zz a -tzip -mx=9 "$ZIP" . -xr!'.DS_Store' )

echo "Готово: $ZIP"
