#!/usr/bin/env bash

git clone https://github.com/doubleleft/hook.git --depth=1
cd hook
make
cd ../

git clone https://github.com/doubleleft/hook-cli.git --depth=1
cd hook-cli
make
cd ../
