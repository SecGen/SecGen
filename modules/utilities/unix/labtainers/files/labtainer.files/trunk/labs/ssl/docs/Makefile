ssl.pdf: ssl.tex header.tex
	latex ssl
	pdflatex -jobname=ssl ssl
	pdflatex -jobname=ssl ssl

clean:
	rm -fr auto
	rm -f ssl.aux ssl.log ssl.pdf ssl.dvi ssl.out
