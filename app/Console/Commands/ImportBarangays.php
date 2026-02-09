<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Barangay;

class ImportBarangays extends Command
{
    protected $signature = 'import:barangays {--file=storage/app/barangays.csv}';
    protected $description = 'Import barangays with PSGC codes and coordinates';

    public function handle()
    {
        $path = base_path($this->option('file'));

        if (!file_exists($path)) {
            $this->error("CSV file not found: {$path}");
            return Command::FAILURE;
        }

        $this->info('📥 Importing barangays...');

        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        $count = 0;

        foreach ($rows as $row) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            Barangay::updateOrCreate(
                [
                    // ✅ UNIQUE KEY
                    'psgc_barangay' => $data['psgc_barangay'],
                ],
                [
                    'region'            => $data['region'],
                    'psgc_region'       => $data['psgc_region'],
                    'province'          => $data['province'] ?: null,
                    'psgc_province'     => $data['psgc_province'] ?: null,
                    'city_municipality' => $data['city_municipality'],
                    'psgc_city'         => $data['psgc_city'],
                    'barangay'          => $data['barangay'],
                    'latitude'          => (float) $data['latitude'],
                    'longitude'         => (float) $data['longitude'],
                ]
            );

            $count++;
        }

        $this->info("✅ {$count} barangays imported successfully.");

        return Command::SUCCESS;
    }
}
