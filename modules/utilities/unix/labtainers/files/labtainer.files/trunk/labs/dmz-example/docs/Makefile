dmz-example.pdf: dmz-example.tex header.tex
	latex dmz-example
	pdflatex -jobname=dmz-example dmz-example
	pdflatex -jobname=dmz-example dmz-example

clean:
	rm -fr auto
	rm -f dmz-example.aux dmz-example.log dmz-example.pdf dmz-example.dvi dmz-example.out
