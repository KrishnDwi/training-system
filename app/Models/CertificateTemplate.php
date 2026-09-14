<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'background_image_path',
        'original_filename',
        'page_width_mm',
        'page_height_mm',
        'fields_config',
    ];

    protected $casts = [
        'fields_config' => 'array',
    ];

    /**
     * Karena ini singleton (cuma 1 desain sertifikat dipakai untuk semua
     * training), ambil baris pertama yang ada — null kalau HR belum pernah
     * upload template sama sekali.
     */
    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }

    /**
     * Konfigurasi default posisi teks (mm, dari pojok kiri-atas) untuk
     * kanvas A4 landscape (297 x 210mm) — dipakai kalau HR belum pernah
     * mengatur posisi sendiri.
     */
    public static function defaultFieldsConfig(): array
    {
        return [
            'name' => ['enabled' => true, 'x' => 148, 'y' => 100, 'font_size' => 28, 'color' => '#1a1a1a', 'align' => 'center'],
            'module' => ['enabled' => true, 'x' => 148, 'y' => 120, 'font_size' => 16, 'color' => '#333333', 'align' => 'center'],
            'date' => ['enabled' => true, 'x' => 148, 'y' => 140, 'font_size' => 12, 'color' => '#555555', 'align' => 'center'],
            'score' => ['enabled' => false, 'x' => 148, 'y' => 155, 'font_size' => 12, 'color' => '#555555', 'align' => 'center'],
        ];
    }

    public function getFieldConfig(string $field): array
    {
        $config = $this->fields_config[$field] ?? [];

        return array_merge(static::defaultFieldsConfig()[$field], $config);
    }

    /**
     * Render sertifikat jadi PDF. Dipakai baik untuk preview (data contoh,
     * oleh HR) maupun sertifikat asli (data karyawan sebenarnya).
     */
    public function renderPdf(array $values)
    {
        $backgroundPath = \Illuminate\Support\Facades\Storage::disk('local')->path($this->background_image_path);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.template', [
            'template' => $this,
            'backgroundPath' => $backgroundPath,
            'values' => $values,
        ])->setPaper([0, 0, $this->mmToPt($this->page_width_mm), $this->mmToPt($this->page_height_mm)]);
    }

    protected function mmToPt(float $mm): float
    {
        return $mm * 2.83465; // 1mm = 2.83465pt
    }
}
