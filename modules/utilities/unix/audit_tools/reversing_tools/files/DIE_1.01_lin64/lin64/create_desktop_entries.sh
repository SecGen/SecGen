#!/bin/sh

FindPath()
{
    fullpath="`echo $1 | grep /`"
    if [ "$fullpath" = "" ]; then
        oIFS="$IFS"
        IFS=:
        for path in $PATH
        do if [ -x "$path/$1" ]; then
            if [ "$path" = "" ]; then
                path="."
            fi
            fullpath="$path/$1"
            break
        fi
    done
    IFS="$oIFS"
    fi
    if [ "$fullpath" = "" ]; then
        fullpath="$1"
    fi

    # Is the sed/ls magic portable?
    if [ -L "$fullpath" ]; then
        #fullpath="`ls -l "$fullpath" | awk '{print $11}'`"
        fullpath=`ls -l "$fullpath" |sed -e 's/.* -> //' |sed -e 's/\*//'`
    fi
    dirname $fullpath
}


here=$(cd "$(dirname "$0")"; pwd)
echo $here
echo "Create entry for DIE..."
cat ./desktop/die.desktop \
	| sed "s:%%path%%:$here:" \
	> ~/.local/share/applications/die.desktop
echo "Create entry for DIE lite..."
cat ./desktop/diel.desktop \
	| sed "s:%%path%%:$here:" \
	> ~/.local/share/applications/diel.desktop
