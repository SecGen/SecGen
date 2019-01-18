onewayhash.pdf: Crypto_Hash.tex header.tex
	latex Crypto_Hash
	pdflatex -jobname=onewayhash Crypto_Hash
	pdflatex -jobname=onewayhash Crypto_Hash

clean:
	rm -fr auto
	rm -f Crypto_Hash.aux Crypto_Hash.log Crypto_Hash.pdf
