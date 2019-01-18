radius.pdf: radius.tex header.tex
	latex radius
	pdflatex -jobname=radius radius
	pdflatex -jobname=radius radius

clean:
	rm -fr auto
	rm -f radius.aux radius.log radius.pdf radius.dvi radius.out
