#!/bin/bash
#
# Build the pdf documents for all labs.
# Intended to be run by developers who use SVN to populate their labs/ directory.
# (PDF documents are not kept in svn, and are typically created when a distribution
# is made)
cd ../labs
llist=$(ls)
for lab in $llist; do
        cd $lab
        if [[ -d docs ]]; then
            cd docs
            if [[ -f Makefile ]]; then
                make
            else
                doc=$lab.docx
                pdf=$lab.pdf
                if [[ -f $doc ]]; then
                    if [[ ! -f $pdf ]] || [[ "$pdf" -ot "$doc" ]]; then
                        soffice --convert-to pdf $doc --headless
                    else
                        echo $pdf is up to date.
                    fi
                fi
            fi
            cd ../
        fi
        cd ../
done
cd ../docs/labdesigner
make
cd ../student
make
cd ../instructor
make
