<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    public function edit()
    {
        $template = CertificateTemplate::current();
        $fields = $template
            ? array_merge(CertificateTemplate::defaultFieldsConfig(), $template->fields_config)
            : CertificateTemplate::defaultFieldsConfig();

        return view('certificate-template.edit', compact('template', 'fields'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'background_image' => ['nullable', 'image', 'max:5120'], // maks 5MB, opsional kalau cuma ganti posisi
            'page_width_mm' => ['required', 'integer', 'min:100', 'max:500'],
            'page_height_mm' => ['required', 'integer', 'min:100', 'max:500'],
            'fields' => ['required', 'array'],
            'fields.*.enabled' => ['nullable', 'boolean'],
            'fields.*.x' => ['required', 'numeric', 'min:0'],
            'fields.*.y' => ['required', 'numeric', 'min:0'],
            'fields.*.font_size' => ['required', 'integer', 'min:6', 'max:100'],
            'fields.*.color' => ['required', 'string', 'max:7'],
            'fields.*.align' => ['required', 'in:left,center,right'],
        ]);

        $template = CertificateTemplate::current();

        $fieldsConfig = [];
        foreach ($validated['fields'] as $key => $field) {
            $fieldsConfig[$key] = [
                'enabled' => $request->boolean("fields.{$key}.enabled"),
                'x' => (float) $field['x'],
                'y' => (float) $field['y'],
                'font_size' => (int) $field['font_size'],
                'color' => $field['color'],
                'align' => $field['align'],
            ];
        }

        $data = [
            'page_width_mm' => $validated['page_width_mm'],
            'page_height_mm' => $validated['page_height_mm'],
            'fields_config' => $fieldsConfig,
        ];

        if ($request->hasFile('background_image')) {
            // Hapus file lama supaya tidak menumpuk storage
            if ($template && Storage::disk('local')->exists($template->background_image_path)) {
                Storage::disk('local')->delete($template->background_image_path);
            }

            $file = $request->file('background_image');
            $data['background_image_path'] = $file->store('certificate-templates', 'local');
            $data['original_filename'] = $file->getClientOriginalName();
        } elseif (!$template) {
            return back()->withErrors(['background_image' => 'Upload gambar background sertifikat terlebih dahulu.']);
        }

        if ($template) {
            $template->update($data);
        } else {
            CertificateTemplate::create($data);
        }

        return redirect()
            ->route('certificate-template.edit')
            ->with('success', 'Template sertifikat berhasil disimpan.');
    }

    /**
     * Render PDF pakai data contoh (dummy) — supaya HR bisa cek posisi teks
     * tanpa perlu benar-benar ada karyawan yang lulus post-test dulu.
     */
    public function preview()
    {
        $template = CertificateTemplate::current();

        abort_unless($template, 404, 'Upload template sertifikat terlebih dahulu.');

        $pdf = $template->renderPdf([
            'name' => 'Nama Karyawan Contoh',
            'module' => 'Nama Modul Training Contoh',
            'date' => now()->translatedFormat('d F Y'),
            'score' => '95',
        ]);

        return $pdf->stream('preview-sertifikat.pdf');
    }
}
