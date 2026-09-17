<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserCsvImporter
{
    public const EXPECTED_HEADERS = ['name', 'email', 'role', 'position', 'line_manager_email', 'is_active'];

    /**
     * @return array<int, array{row: int, name: ?string, email: ?string, status: string, message: ?string, generated_password: ?string}>
     */
    public function import(string $path): array
    {
        $rows = $this->readRows($path);
        $results = [];

        // Pass 1: create or update every row's core fields, without touching
        // line_manager_id — a manager may appear later in the same file.
        foreach ($rows as $number => $row) {
            $results[$number] = $this->processRow($number, $row);
        }

        // Pass 2: now that every user in this batch exists, resolve each
        // row's line_manager_email against the database.
        foreach ($rows as $number => $row) {
            if ($results[$number]['status'] === 'skipped') {
                continue;
            }

            $this->applyLineManager($number, $row, $results);
        }

        return array_values($results);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readRows(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle, escape: '\\');

        if ($header === false) {
            fclose($handle);

            return [];
        }

        // Strip a UTF-8 BOM Excel sometimes prepends to the first header cell.
        $header[0] = preg_replace('/^\x{FEFF}/u', '', (string) $header[0]);
        $header = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->snake()->value(), $header);

        $rows = [];
        $number = 1;

        while (($line = fgetcsv($handle, escape: '\\')) !== false) {
            if (count($line) === 1 && trim((string) $line[0]) === '') {
                continue;
            }

            $row = [];
            foreach ($header as $index => $key) {
                $row[$key] = isset($line[$index]) ? trim((string) $line[$index]) : '';
            }

            $rows[$number] = $row;
            $number++;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  array<string, string>  $row
     * @return array{row: int, name: ?string, email: ?string, status: string, message: ?string, generated_password: ?string}
     */
    private function processRow(int $number, array $row): array
    {
        $name = $row['name'] ?? '';
        $email = $row['email'] ?? '';

        $base = ['row' => $number, 'name' => $name ?: null, 'email' => $email ?: null, 'generated_password' => null];

        if ($name === '' && $email === '') {
            return [...$base, 'status' => 'skipped', 'message' => 'Empty row.'];
        }

        if ($name === '') {
            return [...$base, 'status' => 'skipped', 'message' => 'Missing name.'];
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [...$base, 'status' => 'skipped', 'message' => 'Missing or invalid email.'];
        }

        $role = UserRole::tryFrom(Str::lower(trim($row['role'] ?? '')));

        if ($role === null) {
            return [...$base, 'status' => 'skipped', 'message' => 'Role must be admin, reviewer or employee.'];
        }

        $position = ($row['position'] ?? '') !== '' ? $row['position'] : null;
        $isActive = $this->parseBoolean($row['is_active'] ?? '');

        $existing = User::where('email', $email)->first();
        $generatedPassword = null;

        if ($existing) {
            $existing->update([
                'name' => $name,
                'role' => $role,
                'position' => $position,
                'is_active' => $isActive,
            ]);

            $user = $existing;
            $status = 'updated';
            AuditLog::record('user.imported', $user, "Updated via CSV import: {$user->name} ({$user->role->value})");
        } else {
            $generatedPassword = Str::password(12);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($generatedPassword),
                'role' => $role,
                'position' => $position,
                'is_active' => $isActive,
                'email_verified_at' => now(),
            ]);

            $status = 'created';
            AuditLog::record('user.imported', $user, "Created via CSV import: {$user->name} ({$user->role->value})");
        }

        return [...$base, 'status' => $status, 'message' => null, 'generated_password' => $generatedPassword];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<int, array<string, mixed>>  $results
     */
    private function applyLineManager(int $number, array $row, array &$results): void
    {
        $managerEmail = trim($row['line_manager_email'] ?? '');

        if ($managerEmail === '') {
            return;
        }

        $email = $row['email'] ?? '';
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $manager = User::where('email', $managerEmail)->first();

        if (! $manager) {
            $results[$number]['message'] = "Line manager \"{$managerEmail}\" not found — left unset.";

            return;
        }

        if ($manager->id === $user->id) {
            $results[$number]['message'] = 'Line manager cannot be the same person — left unset.';

            return;
        }

        $user->update(['line_manager_id' => $manager->id]);
    }

    private function parseBoolean(string $value): bool
    {
        $value = Str::lower(trim($value));

        if ($value === '') {
            return true;
        }

        return ! in_array($value, ['0', 'no', 'false', 'inactive', 'n'], true);
    }
}
