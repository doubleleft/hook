#!/usr/bin/env bash

git clone https://github.com/doubleleft/hook.git
cd hook
make
cd ../

git clone https://github.com/doubleleft/hook-cli.git
cd hook-cli
make
cd ../
