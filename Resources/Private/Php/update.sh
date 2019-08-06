#!/bin/bash
composer install
find . -name ".git" -exec rm -rf {} \;
