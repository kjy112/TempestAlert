#!/usr/bin/env sh
SRC_DIR="`pwd`"
cd "`dirname "$0"`"
cd "../phpdocumentor/phpdocumentor/bin"
BIN_TARGET="`pwd`/phpdoc.php"
cd "$SRC_DIR"
"$BIN_TARGET" "$@"
