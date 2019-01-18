snort.pdf: snort.tex header.tex
	latex snort
	pdflatex -jobname=snort snort
	pdflatex -jobname=snort snort

clean:
	rm -fr auto
	rm -f snort.aux snort.log snort.pdf snort.dvi snort.out
