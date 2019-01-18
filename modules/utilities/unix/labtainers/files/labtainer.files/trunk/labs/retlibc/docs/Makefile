retlibc.pdf: Return_to_libc.tex header.tex
	latex Return_to_libc
	pdflatex -jobname=retlibc Return_to_libc
	pdflatex -jobname=retlibc Return_to_libc

clean:
	rm -fr auto
	rm -f *.aux *.log *.pdf *.dvi *.out
