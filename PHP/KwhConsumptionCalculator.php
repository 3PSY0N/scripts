#!/usr/bin/env php
<?php

class KwhConsumptionCalculator
{
    private const KILOWATTHOUR_PRICE       = 2276;
    private const DURATION                 = [0, 1, 0];
    private const MACHINE_CONSUMPTION_WATT = 100;

    private const OPT_SHORT = "p:d:w:h";
    private array|false $options;
    private int|float   $kilowatthourPrice;
    private array       $totalDuration;
    private int         $machineConsumptionWatt;

    /**
     * Constructor of the KwhConsumptionCalculator class.
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

        $this->kilowatthourPrice = isset($this->options['p']) ? (floatval($this->options['p']) / 10000) : (self::KILOWATTHOUR_PRICE / 10000);
        $this->totalDuration = isset($this->options['d']) ? array_map('intval', explode(',', $this->options['d'])) : self::DURATION;
        $this->machineConsumptionWatt = isset($this->options['w']) ? intval($this->options['w']) : self::MACHINE_CONSUMPTION_WATT;

        echo $this->getData();
    }

    /**
     * Displays help information for the RemainingFilamentCalculator.
     *
     * @return string|null
     */
    private function displayHelp(): ?string
    {
        $kwhPrice = self::KILOWATTHOUR_PRICE;
        $kwhPriceDivided = self::KILOWATTHOUR_PRICE / 10000;
        $machineConsumptionWatt = self::MACHINE_CONSUMPTION_WATT;

        return <<<HELP
        
        \033[33m┌─────  kWh Consumption Calculator  ─────\e[0m
        \033[33m│\e[0m
        \033[33m│\e[0m Description:  
        \033[33m│\e[0m   This program calculates the cost of use in €uro and the consumption (kWh) of a device over time. 
        \033[33m│\e[0m
        \033[33m│\e[0m Usage: 
        \033[33m│\e[0m   -p {$kwhPrice} -w {$machineConsumptionWatt} -d 0,2,30
        \033[33m│\e[0m
        \033[33m│\e[0m Options:
        \033[33m│\e[0m   -p <\033[32mint\e[0m>  : Price per kWh (in euro).
        \033[33m│\e[0m                 Example: {$kwhPrice} for {$kwhPriceDivided}€.
        \033[33m│\e[0m   -d <\033[32md,h,m\e[0m>: Define machine operating time (in days, hours, minutes).
        \033[33m│\e[0m                 Example: -d 1,1,30 for 1 day, 1 hour and 30 minutes.
        \033[33m│\e[0m   -w <\033[32mint\e[0m>  : Machine consumption in watts. Example: -w {$machineConsumptionWatt} for {$machineConsumptionWatt} watts.
        \033[33m│\e[0m
        \033[33m└─────\e[0m

        HELP;
    }

    /**
     * Calculates the duration in decimal hours.
     *
     * @param array $duration The duration array [days, hours, minutes]
     *
     * @return float The duration in decimal hours
     */
    private function duration(array $duration): float
    {
        $days = 0;
        $hours = 0;
        $minutes = 0;

        match (count($duration)) {
            1 => $minutes = $duration[0],
            2 => [$hours, $minutes] = $duration,
            3 => [$days, $hours, $minutes] = $duration,
            default => "Duration format is invalid."
        };

        return $days * 24 + $hours + $minutes / 60;
    }

    /**
     * Calculates kWh consumption and cost.
     *
     * @return array An array with results [kilowatthourConsumption, totalPrice, totalTime]
     */
    private function kwhConsumptionCalc(): array
    {
        $totalTime = $this->duration($this->totalDuration);

        $consumptionKilowatthours = $this->machineConsumptionWatt * $totalTime / 1000;

        $totalPrice = $consumptionKilowatthours * $this->kilowatthourPrice;

        return [
            'kilowatthourConsumption' => $consumptionKilowatthours,
            'totalPrice'              => $totalPrice,
            'totalTime'               => $totalTime
        ];
    }

    /**
     * Gets the formatted duration in days, hours, and minutes.
     *
     * @param float $totalTime The total duration in decimal hours
     *
     * @return string The formatted duration
     */
    private function getReverseTotalTime(float $totalTime): string
    {
        $days = floor($totalTime / 24);
        $remainingHours = $totalTime - ($days * 24);
        $hours = floor($totalTime - ($days * 24));
        $minutes = round(($remainingHours - floor($remainingHours)) * 60);

        $duration = [];

        if ($days) {
            $duration['days'] = $days . ' day' . (($days > 1) ? 's' : '');
        }
        if ($hours) {
            $duration['hours'] = $hours . ' hour' . (($hours > 1) ? 's' : '');
        }
        if ($minutes) {
            $duration['minutes'] = $minutes . ' minute' . (($minutes > 1) ? 's' : '');
        }

        return implode(', ', $duration);
    }

    /**
     * Gets the formatted data.
     *
     * @return string The formatted data
     */
    private function getData(): string
    {
        $kilowatthourPrice = $this->kilowatthourPrice;
        $machineConsumptionWatt = $this->machineConsumptionWatt;
        $operatingTime = $this->getReverseTotalTime($this->kwhConsumptionCalc()['totalTime']);

        $costOfUse = round($this->kwhConsumptionCalc()['totalPrice'], 5);
        $totalKwhConsumption = round($this->kwhConsumptionCalc()['kilowatthourConsumption'], 5);

        return <<<INFO

        \e[37mCalculations:\e[0m

        kWh Price      : \e[32m{$kilowatthourPrice}€\e[0m
        Device power   : \e[31m{$machineConsumptionWatt}W\e[0m
        Operating time : \e[33m{$operatingTime}\e[0m
        \e[37m----\e[0m
        Consumption    : \e[36m{$totalKwhConsumption}kWh\e[0m
        Cost of use    : \e[32m{$costOfUse}€\e[0m

        INFO;
    }
}

new KwhConsumptionCalculator();
