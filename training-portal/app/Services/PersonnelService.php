<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PersonnelService
{
    public function all(): array
    {
        $employees = [];
        $url = $this->baseUrl().'/employees';
        $page = 1;

        do {
            try {
                $response = $this->client()->get($url, ['per_page' => 100, 'page' => $page]);
            } catch (ConnectionException $exception) {
                throw new RuntimeException('Personel servisine şu anda ulaşılamıyor.', 0, $exception);
            }

            if (! $response->successful()) {
                throw new RuntimeException('Personel servisi geçerli bir yanıt vermedi.');
            }

            $payload = $response->json();
            $employees = array_merge($employees, $payload['data'] ?? []);
            $lastPage = (int) ($payload['last_page'] ?? 1);
            $page++;
        } while ($page <= $lastPage);

        return $employees;
    }

    public function find(int $employeeId): array
    {
        try {
            $response = $this->client()->get($this->baseUrl().'/employees/'.$employeeId);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Personel servisine şu anda ulaşılamıyor.', 0, $exception);
        }

        if ($response->status() === 404) {
            throw new RuntimeException('Seçilen personel artık aktif değil.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Personel doğrulanamadı.');
        }

        return (array) $response->json('data');
    }

    private function client()
    {
        return Http::acceptJson()
            ->withToken((string) config('services.personnel.token'))
            ->timeout(max(1, (int) config('services.personnel.timeout', 5)))
            ->retry(1, 150, throw: false);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.personnel.base_url'), '/');
    }
}
