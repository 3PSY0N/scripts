#!/usr/bin/env bash
set -euo pipefail

#
# ANSI colors array
# See https://en.wikipedia.org/wiki/ANSI_escape_code#3-bit_and_4-bit
#
fg_colors=(30 31 32 33 34 35 36 37 90 91 92 93 94 95 96 97)
bg_colors=(40 41 42 43 44 45 46 47 100 101 102 103 104 105 106 107)

#
# Print colors function
#
print_colors() {
  for ((i=0; i<${#fg_colors[@]}; i++)); do
      fg="${fg_colors[i]}"
      bg="${bg_colors[i]}"
    printf "│ \e[%smText\e[0m [ %-2s ]  │ \e[%s;%smXXXX\e[0m [ %-3s ] │\n" "$fg" "$fg" "$fg" "$bg" "$bg"
  done
}

#
# Table header
#
echo "┌─────────────────────────────┐"
echo "│      ANSI COLORS CODES      │"
echo "├──────────────┬──────────────┤"
echo "│  Foreground  │  Background  │"
echo "├──────────────┼──────────────┤"

#
# Print colors
#
print_colors;

#
# End of table
#
echo -e "└──────────────┴──────────────┘\n"
echo "Usage example: echo -e \"\e[31;105m Text \e[0m\""

#
# Table Symbols
#
# ┌ ─ ┬ ─ ┐
# ├   ┼   ┤
# └ ─ ┴ ─ ┘
