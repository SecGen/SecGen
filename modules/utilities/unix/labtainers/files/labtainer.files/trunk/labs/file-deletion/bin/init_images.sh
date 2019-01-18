#!/bin/bash
dd if=/dev/zero of=myfs.img bs=1k count=1k 
mkfs.ext2 -F myfs.img
sudo mount -o loop myfs.img mnt
sudo chown student mnt

echo “dumb filler” > mnt/fillerfile
echo “dumb filler” > mnt/fillerfile2
echo “dumb filler” > mnt/fillerfile3
echo “dumb filler” > mnt/fillerfile4
echo “dumb filler” > mnt/fillerfile5
echo “dumb filler” > mnt/fillerfile6
echo “dumb filler” > mnt/fillerfile7
echo “First file created” > mnt/file1
echo “Second file created” > mnt/file2
echo “Third file” > mnt/file3


sudo umount mnt

strings -td myfs.img
