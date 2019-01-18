ldap.pdf: ldap.tex header.tex
	latex ldap
	pdflatex -jobname=ldap ldap
	pdflatex -jobname=ldap ldap

clean:
	rm -fr auto
	rm -f ldap.aux ldap.log ldap.pdf ldap.dvi ldap.out
