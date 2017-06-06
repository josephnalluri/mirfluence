#!/usr/bin/env bash

DEST_DIR="/var/www/bnet.egr.vcu.edu/public_html/mirfluence2"

# Remove previous folder
if [[ -e "$DEST_DIR" ]]; then
    rm -rf "$DEST_DIR"
fi
mkdir -p "$DEST_DIR"

cp -r src/* "$DEST_DIR"
chown -R www-data:www-data "$DEST_DIR"
chown -R mysql:mysql "$DEST_DIR/CSV"
chmod -R a+rwx "$DEST_DIR/CSV"
