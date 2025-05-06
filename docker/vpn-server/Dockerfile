FROM alpine:3.15

# Install OpenVPN and dependencies
RUN apk add --no-cache openvpn easy-rsa bash curl jq openssl iptables

# Set up Easy-RSA environment
WORKDIR /etc/openvpn/easy-rsa
RUN ln -s /usr/share/easy-rsa/* /etc/openvpn/easy-rsa/
RUN mkdir -p /etc/openvpn/ccd /etc/openvpn/certificates /etc/openvpn/logs

# Copy config and scripts
COPY openvpn.conf /etc/openvpn/
COPY scripts/generate_keys.sh /usr/local/bin/
COPY scripts/create_client.sh /usr/local/bin/
COPY scripts/revoke_client.sh /usr/local/bin/

# Make scripts executable
RUN chmod +x /usr/local/bin/generate_keys.sh
RUN chmod +x /usr/local/bin/create_client.sh
RUN chmod +x /usr/local/bin/revoke_client.sh

# Generate server keys if they don't exist
RUN if [ ! -f /etc/openvpn/certificates/ca.crt ]; then /usr/local/bin/generate_keys.sh; fi

# Configure networking
RUN mkdir -p /dev/net
RUN if [ ! -c /dev/net/tun ]; then mknod /dev/net/tun c 10 200; fi
RUN echo "net.ipv4.ip_forward = 1" >> /etc/sysctl.conf

# Run OpenVPN server
CMD ["openvpn", "--config", "/etc/openvpn/openvpn.conf"]

# Expose port
EXPOSE 1194/udp