new_ip_addr = "10.170.92.3"
old_ip_addr = "172.22.6.255"

output = `echo "auto lo\niface lo inet loopback\n\nauto eth0\niface eth0 inet static\n\taddress #{new_ip_addr}" | ssh vagrant@#{old_ip_addr} "sudo sh -c 'cat >/etc/network/interfaces'"`