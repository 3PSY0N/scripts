#!/usr/bin/env bash
set -euo pipefail

install_path="/usr/local/bin"

# Download and install Symfony CLI
echo -e "\n─────────> Download latest symfony binary\n"
curl -sS https://get.symfony.com/cli/installer | bash

# Move Symfony binary to /usr/local/bin
echo -e "\n─────────> Moving symfony binary to $install_path"
sudo mv "$HOME/.symfony5/bin/symfony" "$install_path/symfony"

# Print the newest version
echo -e "\n────────── Installed version ──────────"
symfony -V
