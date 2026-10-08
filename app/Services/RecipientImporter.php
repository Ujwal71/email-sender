<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class RecipientImporter
{
    /**
     * Parse a CSV (header: name,email) into valid and invalid rows.
     *
     * @return array{0: array<int,array{name:string,email:string}>, 1: array<int,array{line:int,value:string,reason:string}>}
     */
    public function parse(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('The file could not be read.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new InvalidArgumentException('The CSV file is empty.');
        }

        $header = array_map(
            fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
            $header
        );
        $nameCol = array_search('name', $header, true);
        $emailCol = array_search('email', $header, true);

        if ($emailCol === false) {
            fclose($handle);
            throw new InvalidArgumentException('The CSV header must be: name,email');
        }

        $valid = [];
        $invalid = [];
        $seen = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // blank line
            }

            $email = strtolower(trim((string) ($row[$emailCol] ?? '')));
            $name = $nameCol === false ? '' : trim(preg_replace('/\s+/', ' ', (string) ($row[$nameCol] ?? '')));

            $fails = Validator::make(['email' => $email], ['email' => 'required|email:rfc|max:255'])->fails();

            if ($fails) {
                $invalid[] = ['line' => $line, 'value' => implode(', ', $row), 'reason' => 'Invalid email address'];
            } elseif (isset($seen[$email])) {
                $invalid[] = ['line' => $line, 'value' => implode(', ', $row), 'reason' => 'Duplicate email'];
            } else {
                $seen[$email] = true;
                $valid[] = ['name' => mb_substr($name !== '' ? $name : $email, 0, 255), 'email' => $email];
            }
        }

        fclose($handle);

        return [$valid, $invalid];
    }
}
