#!/bin/sh

dirs=""
systems=`ls -1 systems/`
for system in $systems; do
    if [ -d systems/$system ]; then
        if [ $system != "jpgraph" ]; then
            if [ "$dirs" != "" ]; then
                dirs="$dirs,systems/$system"
            else
                dirs="systems/$system"
            fi
        fi
    fi
done

set -x
phpdoc -ti 'Money Tracker' -s on -d $dirs -t docs

