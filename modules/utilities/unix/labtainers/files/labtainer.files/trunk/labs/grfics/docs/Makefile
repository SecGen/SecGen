grfics.pdf: grfics.tex header.tex
	latex grfics
	pdflatex -jobname=grfics grfics
	pdflatex -jobname=grfics grfics

clean:
	rm -fr auto
	rm -f grfics.aux grfics.log grfics.pdf grfics.dvi grfics.out
