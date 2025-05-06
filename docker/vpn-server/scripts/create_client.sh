# create_client.sh
#!/bin/bash
set -e

# Check if username is provided
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <username>"
    exit 1
fi

USERNAME=$1
cd /etc/openvpn/easy-rsa

# Generate client key and certificate
./easyrsa --batch gen-req $USERNAME nopass
./easyrsa --batch sign-req client $USERNAME

# Create client config directory if it doesn't exist
mkdir -p /etc/openvpn/clients/$USERNAME

# Create client configuration file
cat > /etc/openvpn/clients/$USERNAME/$USERNAME.ovpn << EOF
client
dev tun
proto udp
remote vpn.vpn4u.io 1194
resolv-retry infinite
nobind
persist-key
persist-tun
remote-cert-tls server
verify-x509-name "VPN4U Server" name
auth-user-pass
cipher AES-256-GCM
verb 3

<ca>
$(cat /etc/openvpn/certificates/ca.crt)
</ca>
<cert>
$(cat /etc/openvpn/easy-rsa/pki/issued/$USERNAME.crt)
</cert>
<key>
$(cat /etc/openvpn/easy-rsa/pki/private/$USERNAME.key)
</key>
EOF

echo "Client configuration created at /etc/openvpn/clients/$USERNAME/$USERNAME.ovpn"
