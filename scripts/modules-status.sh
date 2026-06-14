#!/usr/bin/env bash
set -euo pipefail

# Показывает модули с невыпущенными изменениями.
# Работает с git-репой исходников модулей (rotor-modules): сравнивает текущее
# состояние каждого модуля с тегом его последнего релиза (<Модуль>-<версия>,
# который создаёт rotor-modules/.github/workflows/update-registry.yml).
#
# Каталог репы модулей определяется так (по приоритету):
#   1. $ROTOR_MODULES_DIR
#   2. по симлинку modules/* (rotor/modules/Offer -> ../../rotor-modules/Offer)
#   3. соседний каталог ../rotor-modules
#
# Состояния:
#   ● правки    — код менялся, но версия в module.php та же, что у релиза → подними версию
#   ○ к релизу  — версия уже поднята, тег ещё не создан → запушь, CI соберёт релиз
#   (чистые модули не выводятся)
#
# Использование:
#   ./scripts/modules-status.sh            # с фетчем тегов
#   ./scripts/modules-status.sh --no-fetch # без обращения к origin
#   ./scripts/modules-status.sh -v <Mod>   # показать diff конкретного модуля

coreRoot="$(cd "$(dirname "$0")/.." && pwd)"

fetch=1
showDiff=""
while [ "$#" -gt 0 ]; do
    case "$1" in
        --no-fetch) fetch=0 ;;
        -v) showDiff="${2:?Укажи модуль: -v Offer}"; shift ;;
        *) echo "Неизвестный аргумент: $1" >&2; exit 1 ;;
    esac
    shift
done

# Определяем каталог репы модулей
modulesDir="${ROTOR_MODULES_DIR:-}"
if [ -z "$modulesDir" ]; then
    link="$(find "$coreRoot/modules" -maxdepth 1 -type l 2>/dev/null | head -1 || true)"
    if [ -n "$link" ]; then
        modulesDir="$(dirname "$(cd "$(dirname "$link")" && realpath "$link")")"
    elif [ -d "$coreRoot/../rotor-modules" ]; then
        modulesDir="$(cd "$coreRoot/../rotor-modules" && pwd)"
    fi
fi

if [ -z "$modulesDir" ] || [ ! -d "$modulesDir/.git" ]; then
    echo "Не найдена git-репа модулей. Укажи путь: ROTOR_MODULES_DIR=/path/to/rotor-modules $0" >&2
    exit 1
fi

cd "$modulesDir"

if [ "$fetch" -eq 1 ]; then
    git fetch --tags -q origin 2>/dev/null || echo "warning: не удалось фетчить теги, статус по локальным" >&2
fi

version_of() {
    sed -n "s/.*'version'[[:space:]]*=>[[:space:]]*'\([^']*\)'.*/\1/p" "$1/module.php" 2>/dev/null | head -1
}

# Подробный diff одного модуля
if [ -n "$showDiff" ]; then
    ver="$(version_of "$showDiff")"
    [ -n "$ver" ] || { echo "Нет $showDiff/module.php или версии" >&2; exit 1; }
    tag="$showDiff-$ver"
    if git rev-parse -q --verify "refs/tags/$tag" >/dev/null; then
        git diff "$tag" -- "$showDiff/"
    else
        echo "Тег релиза $tag не найден — версия ещё не выпущена."
    fi
    exit 0
fi

pending=0
for file in $(find . -maxdepth 2 -name 'module.php' | sort); do
    mod="$(basename "$(dirname "$file")")"
    ver="$(version_of "$mod")"
    [ -n "$ver" ] || continue
    tag="$mod-$ver"

    if ! git rev-parse -q --verify "refs/tags/$tag" >/dev/null; then
        printf '  \033[33m○ к релизу\033[0m  %-14s %-8s версия поднята, тег %s ещё не создан\n' "$mod" "$ver" "$tag"
        pending=$((pending + 1))
        continue
    fi

    changed="$(git diff --name-only "$tag" -- "$mod/" | wc -l | tr -d ' ')"
    if [ "$changed" -ne 0 ]; then
        dirty=""
        [ -n "$(git status --porcelain -- "$mod/")" ] && dirty=" (есть незакоммиченное)"
        printf '  \033[31m● правки\033[0m    %-14s %-8s %s файл(ов) изменено%s\n' "$mod" "$ver" "$changed" "$dirty"
        pending=$((pending + 1))
    fi
done

echo
if [ "$pending" -eq 0 ]; then
    echo "Все модули выпущены — невыпущенных изменений нет."
else
    echo "Модулей с невыпущенными изменениями: $pending"
    echo "Подробный diff:  ./scripts/modules-status.sh -v <Модуль>"
fi
