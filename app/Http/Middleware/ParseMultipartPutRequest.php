<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartPutRequest
{
    /**
     * PHP popola $_POST e $_FILES solo per le richieste POST: con PUT/PATCH in
     * multipart/form-data il body arriva ma campi e file restano vuoti, e
     * l'endpoint risponde 200 senza aggiornare nulla. Qui il body viene
     * parsato a mano e campi/file vengono iniettati nella request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getRealMethod(), ['PUT', 'PATCH'], true)
            && str_starts_with(strtolower((string) $request->header('Content-Type')), 'multipart/form-data')
            && $request->request->count() === 0
            && count($request->files->all()) === 0) {
            $this->parseBody($request);
        }

        return $next($request);
    }

    private function parseBody(Request $request): void
    {
        if (!preg_match('/boundary=(?:"([^"]+)"|([^;\s]+))/i', (string) $request->header('Content-Type'), $match)) {
            return;
        }

        $boundary = $match[1] !== '' ? $match[1] : $match[2];
        $pairs = [];
        $files = [];

        foreach (explode('--' . $boundary, $request->getContent()) as $part) {
            // Preambolo vuoto e chiusura finale "--"
            if (!str_starts_with($part, "\r\n")) {
                continue;
            }

            $sections = explode("\r\n\r\n", substr($part, 2), 2);
            if (count($sections) !== 2) {
                continue;
            }

            [$rawHeaders, $content] = $sections;
            if (str_ends_with($content, "\r\n")) {
                $content = substr($content, 0, -2);
            }

            if (!preg_match('/\bname="([^"]*)"/i', $rawHeaders, $nameMatch)) {
                continue;
            }
            $name = $nameMatch[1];

            if (preg_match('/\bfilename="([^"]*)"/i', $rawHeaders, $fileMatch)) {
                // Campo file lasciato vuoto dal client
                if ($fileMatch[1] === '') {
                    continue;
                }

                $mimeType = preg_match('/^Content-Type:\s*(.+)$/im', $rawHeaders, $typeMatch)
                    ? trim($typeMatch[1])
                    : null;

                $this->setNested($files, $name, $this->storeTemporaryFile($content, $fileMatch[1], $mimeType));

                continue;
            }

            $pairs[] = urlencode($name) . '=' . urlencode($content);
        }

        // parse_str gestisce i nomi annidati (es. extra_fields[main_location][lat])
        parse_str(implode('&', $pairs), $fields);

        $request->request->add($fields);
        $request->files->add($files);
    }

    private function storeTemporaryFile(string $content, string $originalName, ?string $mimeType): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'put_upload_');
        file_put_contents($path, $content);

        register_shutdown_function(static function () use ($path) {
            if (is_file($path)) {
                @unlink($path);
            }
        });

        // test=true: il file non arriva da un upload POST, quindi is_uploaded_file() fallirebbe
        return new UploadedFile($path, $originalName, $mimeType, UPLOAD_ERR_OK, true);
    }

    /**
     * Inserisce un valore in un array seguendo un nome con notazione a parentesi (es. "files[0]").
     */
    private function setNested(array &$target, string $name, mixed $value): void
    {
        preg_match_all('/\[([^\]]*)\]/', $name, $matches);
        $keys = array_merge([strstr($name, '[', true) ?: $name], $matches[1]);

        $ref = &$target;
        foreach ($keys as $index => $key) {
            $isLast = $index === count($keys) - 1;

            if ($key === '') {
                $ref[] = $isLast ? $value : [];
                $key = array_key_last($ref);
            } elseif ($isLast) {
                $ref[$key] = $value;
            } elseif (!isset($ref[$key]) || !is_array($ref[$key])) {
                $ref[$key] = [];
            }

            if (!$isLast) {
                $ref = &$ref[$key];
            }
        }
    }
}
