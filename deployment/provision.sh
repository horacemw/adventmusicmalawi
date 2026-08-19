#!/usr/bin/env bash
#
# One-shot server provisioning for a fresh Hetzner Ubuntu 24.04 box.
# Run as root (Hetzner's default user), then reboot and SSH in as `deploy`.
#
# Usage:
#   scp deployment/provision.sh root@<IP>:/tmp/
#   ssh root@<IP> 'DEPLOY_USER_PUBKEY="ssh-ed25519 AAAA... you@laptop" bash /tmp/provision.sh'
#
# Env vars:
#   DEPLOY_USER          — name of the deploy user (default: deploy)
#   DEPLOY_USER_PUBKEY   — SSH public key line to install for the deploy user (required)
#   TIMEZONE             — system timezone (default: Africa/Blantyre)
#   SWAP_MB              — swap file size in MB (default: 2048)

set -euo pipefail

DEPLOY_USER="${DEPLOY_USER:-deploy}"
TIMEZONE="${TIMEZONE:-Africa/Blantyre}"
SWAP_MB="${SWAP_MB:-2048}"

if [ -z "${DEPLOY_USER_PUBKEY:-}" ]; then
    echo "ERROR: DEPLOY_USER_PUBKEY must be set." >&2
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "ERROR: must be run as root." >&2
    exit 1
fi

echo "==> Setting timezone to ${TIMEZONE}"
timedatectl set-timezone "$TIMEZONE"

echo "==> Updating apt + installing base packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y
apt-get install -y \
    ca-certificates curl gnupg lsb-release \
    ufw fail2ban unattended-upgrades \
    git rsync unzip jq htop \
    apt-transport-https software-properties-common

echo "==> Enabling unattended security upgrades"
dpkg-reconfigure -f noninteractive unattended-upgrades

echo "==> Installing Docker + Compose plugin"
if ! command -v docker >/dev/null 2>&1; then
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" \
        > /etc/apt/sources.list.d/docker.list
    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    systemctl enable --now docker
fi

echo "==> Creating deploy user: ${DEPLOY_USER}"
if ! id -u "$DEPLOY_USER" >/dev/null 2>&1; then
    adduser --disabled-password --gecos "" "$DEPLOY_USER"
fi
usermod -aG docker,sudo "$DEPLOY_USER"

install -d -m 0700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh"
echo "$DEPLOY_USER_PUBKEY" > "/home/$DEPLOY_USER/.ssh/authorized_keys"
chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh/authorized_keys"
chmod 0600 "/home/$DEPLOY_USER/.ssh/authorized_keys"

# Passwordless sudo so deploy scripts can restart services
echo "$DEPLOY_USER ALL=(ALL) NOPASSWD:ALL" > "/etc/sudoers.d/90-$DEPLOY_USER"
chmod 0440 "/etc/sudoers.d/90-$DEPLOY_USER"

echo "==> Hardening SSH (disable root login + password auth)"
sed -i -E \
    -e 's/^#?PermitRootLogin.*/PermitRootLogin no/' \
    -e 's/^#?PasswordAuthentication.*/PasswordAuthentication no/' \
    -e 's/^#?PubkeyAuthentication.*/PubkeyAuthentication yes/' \
    /etc/ssh/sshd_config
systemctl restart ssh

echo "==> Configuring firewall (ufw)"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo "==> Enabling fail2ban"
systemctl enable --now fail2ban

echo "==> Adding swap file (${SWAP_MB}MB)"
if [ ! -f /swapfile ]; then
    fallocate -l "${SWAP_MB}M" /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "==> Preparing app directory"
install -d -m 0755 -o "$DEPLOY_USER" -g "$DEPLOY_USER" /srv/malawiadventistmusic

echo
echo "==== Provisioning complete ====="
echo "Log in from your laptop:"
echo "    ssh ${DEPLOY_USER}@$(curl -s -4 https://ifconfig.io || echo YOUR_IP)"
echo
