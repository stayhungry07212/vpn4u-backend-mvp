# revoke_client.sh
#!/bin/bash
set -e

# Check if username is provided
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <username>"
    exit 1
fi

USERNAME=$1
cd /etc/openvpn/easy-rsa

# Revoke the client certificate
./easyrsa --batch revoke $USERNAME
./easyrsa --batch gen-crl

# Update the CRL on the OpenVPN server
cp /etc/openvpn/easy-rsa/pki/crl.pem /etc/openvpn/

# Remove client files
rm -rf /etc/openvpn/clients/$USERNAME

echo "Client $USERNAME has been revoked"
