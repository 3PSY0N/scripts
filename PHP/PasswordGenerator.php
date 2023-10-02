#!/usr/bin/env php
<?php

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

class PasswordGenerator
{
    private const OPT_SHORT = "l:c:sm:n:h";
    private array|false $options;

    /**
     * @throws Exception
     */

    /**
     * Constructor for PasswordGenerator class.
     * Parses command-line options and generates passwords or displays help.
     *
     * @throws Exception
     * @return void
     */
    #[NoReturn]
    public function __construct()
    {
        $this->options = getopt(self::OPT_SHORT);

        // Display help message if -h option is provided.
        if (isset($this->options['h'])) {
            print $this->displayHelp();
            exit(0);
        }

        // Get options values.
        $length = intval($this->options['l'] ?? 15);
        $case = intval($this->options['c'] ?? 3);
        $useSpecialChar = isset($this->options['s']);
        $customChars = $this->options['m'] ?? null;

        // Check for invalid arguments.
        if ($length < 1 || $case < 1 || $case > 3 ) {
            $this->invalid();
        }

        // Loop to generate multiple passwords if -n option is provided.
        if (isset($this->options['n'])) {
            $number = intval($this->options['n']);

            if ($number === 0) {
                $this->invalid();
            }

            for ($i = 0; $i < $number; ++$i) {
                echo $this->generate($length, $case, $useSpecialChar, $customChars) . "\n";
            }

            exit(0);
        }

        // Generate a single password.
        echo $this->generate($length, $case, $useSpecialChar, $customChars) . "\n";
        exit(0);
    }

    /**
     * Display a help message explaining how to use the password generator.
     *
     * @return string Help message.
     */
    private function displayHelp(): string
    {
        return <<<HELP
        
        \033[33m┌─────  Password Generator Help  ─────\e[0m
        \033[33m│\e[0m 
        \033[33m│\e[0m Usage ./PasswordGenerator.php [options...]
        \033[33m│\e[0m 
        \033[33m│\e[0m -l <\033[32mint\e[0m>  Password Length \033[34m(default: 15)\e[0m
        \033[33m│\e[0m -c <\033[32mint\e[0m>  Chars Case : \033[34m(default: 3)\e[0m
        \033[33m│\e[0m             - 1: Lower case
        \033[33m│\e[0m             - 2: Upper case
        \033[33m│\e[0m             - 3: Mixed case
        \033[33m│\e[0m -s        Add special chars [\033[32m-#_$%&@^~<>*+!?=\e[0m]
        \033[33m│\e[0m -m <\033[32mstr\e[0m>  Manual chars list
        \033[33m│\e[0m -n <\033[32mint\e[0m>  Generate <n> passwords
        \033[33m│\e[0m
        \033[33m│\e[0m \033[37mExample with manual chars list:
        \033[33m│\e[0m \033[37m  ./PasswordGenerator.php -l 15 -m 'abcABC123@$%'
        \033[33m│\e[0m
        \033[33m│\e[0m \033[37mNote: with manual chars list, use single quotes to escape special chars.
        \033[33m│\e[0m \033[37mNote: with manual chars list, -c and -s options are ignored.
        \033[33m└─────\e[0m
        
        HELP;
    }

    /**
     * Generate a random password based on specified parameters.
     *
     * @param int     $length         The length of the password to generate.
     * @param int     $case           Specifies the character case (1: lowercase, 2: uppercase, 3: mixed case).
     * @param bool    $useSpecialChar Indicates whether to include special characters.
     * @param ?string $customChars    Custom character set for password generation (null if not provided).
     *
     * @throws Exception
     * @return string Generated password.
     */
    public function generate(int $length, int $case, bool $useSpecialChar, ?string $customChars): string
    {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = "-#_$%&@^~<>*+!?=";

        $dictionary = match ($case) {
            1 => strtolower($letters . $numbers),
            2 => strtoupper($letters . $numbers),
            3 => strtolower($letters) . strtoupper($letters) . $numbers,
        };

        if ($useSpecialChar) {
            $dictionary .= $special;
        }

        $dictionary = str_shuffle($dictionary);

        // If a custom character set is provided, use it instead of the generated one.
        if (!empty($customChars)) {
            if (mb_strlen($customChars) < 2) {
                echo 'Custom chars list is too short.';
                exit(0);
            }
            $dictionary = $this->strShuffleUnicode($customChars);
        }

        $dictionaryLength = mb_strlen($dictionary, '8bit');
        $pieces = [];
        $previousChar = '';

        for ($i = 0; $i < $length; ++$i) {
            // Select a character from the dictionary using modulo to avoid consecutive repetitions.
            $currentChar = $dictionary[$i % $dictionaryLength];

            /**
             * Check if the current character is the same as the previous character.
             * If it is, move to the next character in the dictionary.
             */
            while ($currentChar === $previousChar) {
                $currentChar = $dictionary[++$i % $dictionaryLength];
            }

            $pieces[] = $currentChar;
            $previousChar = $currentChar;
        }

        return implode('', $pieces);
    }

    /**
     * Shuffle characters in a Unicode string to create a randomized version.
     *
     * @param string $str Input Unicode string to shuffle.
     *
     * @return string Shuffled string.
     */
    private function strShuffleUnicode(string $str): string
    {
        $tmp = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
        shuffle($tmp);

        return join("", $tmp);
    }

    /**
     * Handle invalid command-line arguments by displaying an error message and exiting.
     *
     * @return never This method exits the script with an error message.
     */
    private function invalid(): never
    {
        exit("Invalid arguments.\n");
    }
}

new PasswordGenerator();