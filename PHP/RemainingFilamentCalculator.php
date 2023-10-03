#!/usr/bin/env php
<?php

class RemainingFilamentCalculator
{
    private const OPT_SHORT           = "e:w:d:D:p:h";
    private const EMPTY_SPOOL_WEIGHT  = 270;
    private const ACTUAL_SPOOL_WEIGHT = 1270;
    private const FILAMENT_DIAMETER   = 175;
    private const FILAMENT_DENSITY    = 125;
    private const SPOOL_PRICE         = 20;
    private const CURRENCY_SYMBOL     = '€';

    private array|false $options;

    /**
     * Constructor for RemainingFilamentCalculator class.
     * Initializes options and displays help if requested.
     *
     * @return void
     */
    public function __construct()
    {
        $this->options = getopt(self::OPT_SHORT);

        if (isset($this->options['h'])) {
            echo $this->displayHelp();
            exit(0);
        }

        echo $this->showCalculations();
        exit(0);
    }

    /**
     * Parses command-line options and returns an array of input data.
     *
     * @return array
     */
    private function inputData(): array
    {
        return [
            'emptySpoolWeight'  => isset($this->options['e']) ? intval($this->options['e']) : self::EMPTY_SPOOL_WEIGHT,
            'actualSpoolWeight' => isset($this->options['w']) ? intval($this->options['w']) : self::ACTUAL_SPOOL_WEIGHT,
            'filamentDiameter'  => isset($this->options['d']) ? (intval($this->options['d']) / 100) : (self::FILAMENT_DIAMETER / 100),
            'filamentDensity'   => isset($this->options['D']) ? (intval($this->options['D']) / 100) : (self::FILAMENT_DENSITY / 100),
            'spoolPrice'        => isset($this->options['p']) ? intval($this->options['p']) : self::SPOOL_PRICE,
        ];
    }

    /**
     * Displays help information for the RemainingFilamentCalculator.
     *
     * @return string|null
     */
    private function displayHelp(): ?string
    {
        $filamentDiameter = self::FILAMENT_DIAMETER;
        $filamentDiameterDivided = self::FILAMENT_DIAMETER / 100;
        $filamentDensity = self::FILAMENT_DENSITY;
        $filamentDensityDivided = self::FILAMENT_DENSITY / 100;
        $emptySpoolWeight = self::EMPTY_SPOOL_WEIGHT;
        $actualSpoolWeight = self::ACTUAL_SPOOL_WEIGHT;
        $spoolPrice = self::SPOOL_PRICE;
        $currencySymbol = self::CURRENCY_SYMBOL;

        return <<<HELP
        
        \033[33m┌─────  Remaining filament calculator  ─────\e[0m
        \033[33m│\e[0m
        \033[33m│\e[0m Description:  
        \033[33m│\e[0m   This program estimates the total length of filament remaining on a spool. 
        \033[33m│\e[0m
        \033[33m│\e[0m Usage: 
        \033[33m│\e[0m   ./FilamentCalculator.php [options...]
        \033[33m│\e[0m
        \033[33m│\e[0m Options:
        \033[33m│\e[0m   -d <\033[32mint\e[0m>: Filament diameter. (default: {$filamentDiameter} for {$filamentDiameterDivided}mm)
        \033[33m│\e[0m   -D <\033[32mint\e[0m>: Filament density. (default: {$filamentDensity} for {$filamentDensityDivided}g/cm³)
        \033[33m│\e[0m   -e <\033[32mint\e[0m>: Weight of empty spool in grams. (default: {$emptySpoolWeight}) 
        \033[33m│\e[0m   -w <\033[32mint\e[0m>: Current/total spool weight in grams (default: {$actualSpoolWeight})
        \033[33m│\e[0m   -p <\033[32mint\e[0m>: Filament spool price (default: {$spoolPrice}{$currencySymbol})
        \033[33m│\e[0m
        \033[33m│\e[0m Notes: 
        \033[33m│\e[0m   - To calculate the price per meter, use -w <\033[32mnew spool weight\e[0m> -p <\033[32mspool price\e[0m>
        \033[33m│\e[0m   - You should use this option when your spool is new.
        \033[33m│\e[0m
        \033[33m└─────\e[0m
        
        HELP;
    }

    /**
     * Calculates and returns the length of remaining filament.
     *
     * @return float|int
     */
    private function calculateFilamentLength(): float|int
    {
        $data = $this->inputData();
        $emptySpoolWeight  = $data['emptySpoolWeight'];
        $actualSpoolWeight = $data['actualSpoolWeight'];
        $filamentDiameter  = $data['filamentDiameter'];
        $filamentDensity   = $data['filamentDensity'];

        $filamentMass = $actualSpoolWeight - $emptySpoolWeight;

        $ray = $filamentDiameter / 2;
        $crossSection = pi() * pow($ray, 2);

        return $filamentMass / ($filamentDensity * $crossSection);
    }

    /**
     * Generates and returns information about remaining filament and related data.
     *
     * @return string
     */
    private function showCalculations(): string
    {
        $data = $this->inputData();
        $remainingFilament = round($this->calculateFilamentLength(), 2);
        $spoolPricePerMeter = round(($data['spoolPrice'] / $remainingFilament), 4);
        $filamentDiameter = $data['filamentDiameter'];
        $filamentDensity = $data['filamentDensity'];
        $emptySpoolWeight = $data['emptySpoolWeight'];
        $actualSpoolWeight = $data['actualSpoolWeight'];
        $spoolPrice = $data['spoolPrice'];

        return <<<INFO
        
        Remaining filament: \e[32m$remainingFilament\e[0mm

        Informations:
          - Filament diameter:   \e[33m{$filamentDiameter}\e[0mmm
          - Filament density:    \e[33m{$filamentDensity}\e[0mg/cm³
          - Empty spool weight:  \e[33m{$emptySpoolWeight}\e[0mg
          - Actual spool weight: \e[33m{$actualSpoolWeight}\e[0mg
          - Spool price:         \e[33m{$spoolPrice}\e[0m€
          - Price per meter:     \e[33m{$spoolPricePerMeter}\e[0m€/m

        INFO;
    }
}

new RemainingFilamentCalculator();
