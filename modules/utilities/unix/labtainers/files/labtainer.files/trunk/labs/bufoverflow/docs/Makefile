bufoverflow.pdf: Buffer_Overflow.tex header.tex
	latex Buffer_Overflow
	pdflatex -jobname=bufoverflow Buffer_Overflow
	pdflatex -jobname=bufoverflow Buffer_Overflow

clean:
	rm -fr auto
	rm -f Buffer_Overflow.aux Buffer_Overflow.log Buffer_Overflow.pdf
