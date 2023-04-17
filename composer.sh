#!/usr/bin/env bash

docker run --rm --interactive --tty --volume "/${PWD}/app":/app --user $(id -u):$(id -g) composer:1.10 "$@"

