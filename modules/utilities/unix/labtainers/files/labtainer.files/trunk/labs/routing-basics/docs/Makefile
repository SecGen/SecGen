routing-basics.pdf: routing-basics.tex header.tex
	latex routing-basics
	pdflatex -jobname=routing-basics routing-basics
	pdflatex -jobname=routing-basics routing-basics

clean:
	rm -fr auto
	rm -f routing-basics.aux routing-basics.log routing-basics.pdf routing-basics.dvi routing-basics.out
