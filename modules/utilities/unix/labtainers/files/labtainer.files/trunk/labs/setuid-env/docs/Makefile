setuid-env.pdf: Environment_Variable_and_SetUID.tex header.tex
	latex Environment_Variable_and_SetUID
	pdflatex -jobname=setuid-env Environment_Variable_and_SetUID
	pdflatex -jobname=setuid-env Environment_Variable_and_SetUID

clean:
	rm -fr auto
	rm -f *.aux *.log *.pdf *.out *.dvi
