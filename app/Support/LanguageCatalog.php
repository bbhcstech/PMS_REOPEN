<?php

namespace App\Support;

use Illuminate\Database\Connection;

class LanguageCatalog
{
    public static function populate(Connection $connection): void
    {
        $file = fopen(database_path('data/iso-639-3.tab'), 'r');
        if (! $file) throw new \RuntimeException('The bundled language catalog is missing.');
        try {
            $header = fgetcsv($file, 0, "\t");
            $batch = [];
            $flags = ['en' => 'gb', 'bn' => 'bd', 'hi' => 'in', 'fr' => 'fr', 'de' => 'de'];
            while (($values = fgetcsv($file, 0, "\t")) !== false) {
                $row = array_combine($header, $values);
                $code = $row['Part1'] ?: $row['Id'];
                $batch[] = ['code' => $code, 'iso_639_3' => $row['Id'], 'name' => $row['Ref_Name'],
                    'scope' => $row['Scope'], 'type' => $row['Language_Type'],
                    'flag_url' => isset($flags[$code]) ? 'https://flagcdn.com/w20/' . $flags[$code] . '.png' : null];
                if (count($batch) === 100) {
                    $connection->table('languages')->insertOrIgnore($batch);
                    $batch = [];
                }
            }
            if ($batch) $connection->table('languages')->insertOrIgnore($batch);
        } finally {
            fclose($file);
        }
    }
}
