# generate_keys.sh
#!/bin/bash
set -e

# Initialize the PKI
cd /etc/openvpn/easy-rsa
./easyrsa init-pki

# Build CA
./easyrsa --batch --req-cn="VPN4U CA" build-ca nopass

# Generate server keys
./easyrsa --batch --req-cn="VPN4U Server" gen-req server nopass
./easyrsa --batch sign-req server server

# Generate DH parameters
./easyrsa gen-dh

# Copy certificates to the right location
mkdir -p /etc/openvpn/certificates
cp /etc/openvpn/easy-rsa/pki/ca.crt /etc/openvpn/certificates/
cp /etc/openvpn/easy-rsa/pki/issued/server.crt /etc/openvpn/certificates/
cp /etc/openvpn/easy-rsa/pki/private/server.key /etc/openvpn/certificates/
cp /etc/openvpn/easy-rsa/pki/dh.pem /etc/openvpn/certificates/
