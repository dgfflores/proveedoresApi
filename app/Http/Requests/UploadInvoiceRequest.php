<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfc_receptor'        => ['required', 'string', 'regex:/^[A-Z&Ñ]{3,4}\d{6}[A-V1-9][A-Z\d]{2}$/i'],
            'razon_social'        => ['required'],
            'email_acuse'         => ['required', 'email'],
            'email_acuse_confirm' => ['required', 'email', 'same:email_acuse'],
            'codigo_envio'        => ['nullable', 'string', 'max:10'],
            
            // Archivo XML (requerido, extensión .xml, máx 2MB)
            'xml_file'            => ['required', 'file', 'mimetypes:text/xml,application/xml,xml', 'max:2048'],
            
            // PDF Único principal (requerido, extensión .pdf, máx 2MB)
            'pdf_unico'           => ['required', 'file', 'mimes:pdf', 'max:2048'],
            
            // PDFs de soporte (opcional, array de máximo 2 elementos)
            'pdf_files'           => ['nullable', 'array', 'max:2'],
            'pdf_files.*'         => ['file', 'mimes:pdf', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rfc_receptor' => 'RFC receptor',
            'razon_social' => 'Razón social',
            'email_acuse' => 'Email acuse',
            'email_acuse_confirm' => 'Confirmar email acuse',
            'codigo_envio' => 'Código de envío',
            'xml_file' => 'Archivo XML',
            'pdf_unico' => 'PDF único',
            'pdf_files' => 'PDFs de soporte',
        ];
    }
}
