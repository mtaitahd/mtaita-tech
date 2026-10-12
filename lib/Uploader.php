<?php
/**
 * Small, dependency-free image upload helper.
 *
 * The admin screens must never trust the client: the MIME type is sniffed from
 * the file contents with finfo, the extension is derived from that MIME (not
 * from the user supplied name) and the file is stored under a predictable
 * upload directory with a random name.
 */
class Uploader {
    public const MAX_BYTES = 5 * 1024 * 1024; // 5 MB

    /** mime => extension */
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Handle an optional upload.
     *
     * @param array       $file    $_FILES['...'] entry (may be empty)
     * @param string      $subdir  relative to project root, e.g. assets/img/uploads/services
     * @param string|null $oldPath previously stored relative path (removed on success)
     * @param string      $prefix  filename prefix
     * @return array{ok:bool,path:?string,error:?string,uploaded:bool}
     */
    public static function handle(array $file, string $subdir, ?string $oldPath = null, string $prefix = 'img'): array {
        $result = ['ok' => true, 'path' => $oldPath, 'error' => null, 'uploaded' => false];

        if (empty($file) || !isset($file['error'])) {
            return $result; // no file submitted
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $result; // field present but empty
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['ok'] = false;
            $result['error'] = self::errorMessage((int)$file['error']);
            return $result;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $result['ok'] = false;
            $result['error'] = 'Invalid upload.';
            return $result;
        }

        $mime = self::detectMime($file['tmp_name']);
        if ($mime === null || !isset(self::ALLOWED[$mime])) {
            $result['ok'] = false;
            $result['error'] = 'Invalid file type. Only JPG, PNG, WebP and GIF images are allowed.';
            return $result;
        }

        if ((int)$file['size'] > self::MAX_BYTES) {
            $result['ok'] = false;
            $result['error'] = 'File is too large. Maximum size is 5MB.';
            return $result;
        }

        $ext = self::ALLOWED[$mime];
        $dir = rtrim($subdir, '/');
        $absDir = dirname(__DIR__) . '/' . $dir;
        if (!is_dir($absDir) && !@mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            $result['ok'] = false;
            $result['error'] = 'Could not create the upload directory.';
            return $result;
        }

        $filename = self::safeName($prefix) . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $absTarget = $absDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absTarget)) {
            $result['ok'] = false;
            $result['error'] = 'Failed to save the uploaded file.';
            return $result;
        }

        @chmod($absTarget, 0644);

        // Only remove the previous image after the new one is safely stored.
        if ($oldPath && $oldPath !== $dir . '/' . $filename) {
            self::delete($oldPath);
        }

        $result['path'] = $dir . '/' . $filename;
        $result['uploaded'] = true;
        return $result;
    }

    /** Delete a stored relative path if it is inside the uploads directory. */
    public static function delete(?string $relativePath): void {
        if (!$relativePath) {
            return;
        }
        $relativePath = ltrim($relativePath, '/');
        if (strpos($relativePath, 'assets/img/uploads/') !== 0 && strpos($relativePath, 'assets/img/') !== 0) {
            return; // never delete anything outside our asset areas
        }
        $abs = dirname(__DIR__) . '/' . $relativePath;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    private static function detectMime(string $tmp): ?string {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmp);
                finfo_close($finfo);
                if (is_string($mime)) {
                    return $mime;
                }
            }
        }
        if (function_exists('getimagesize')) {
            $info = @getimagesize($tmp);
            if ($info && !empty($info['mime'])) {
                return $info['mime'];
            }
        }
        return null;
    }

    private static function safeName(string $name): string {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9]+/', '_', $name);
        $name = trim((string)$name, '_');
        return $name !== '' ? substr($name, 0, 40) : 'img';
    }

    private static function errorMessage(int $code): string {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large (server limit).';
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partially uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Server is missing a temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Server failed to write the file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload stopped by a server extension.';
            default:
                return 'Upload failed. Please try again.';
        }
    }
}
