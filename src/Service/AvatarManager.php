<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AvatarManager
{
    public function __construct(
        private readonly string $avatarsDir,
    )
    {
    }

    public function handle(UploadedFile $file, ?string $oldFilename = null): string
    {
        // Déterminer le chemin cible + nom unique
        $newName = sprintf('avt_%s.jpg', bin2hex(random_bytes(8)));
        $target = rtrim($this->avatarsDir, '/') . '/' . $newName;

        // Changer l'image source selon MIME
        $mime = $file->getMimeType();
        $src = match ($mime) {
            'image/jpg' => imagecreatefromjpg($file->getPathname()),
            'image/png' => imagecreatefrompng($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default => null,
        };

        if(!$src){
            throw new \RuntimeException('Format d\'image non supporté.');
        }

        // Auto-rotation EXIF (JPEG uniquement)
        if($mime === 'image/jpeg'){
            $this->autorotateIfNeeded($file->getPathname(), $src);
        }

        // Redimensionnement max 512
        [$w, $h] = [imagesx($src), imagesy($src)];
        $max = 512;
        if($w > $max || $h > $max){
            $ratio = min($max / $w, $h / $max);
            $nw = max(1, (int) round($w * $ratio));
            $nh = max(1, (int) round($h * $ratio));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, true);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        // Réencodage en JPEG ( qualité 85 ) => EXIF supprimé
        if(!imagejpeg($src, $target, 85)){
            imagedestroy($src);
            throw new \RuntimeException('Impossible d\'enregistrer l\'avatar.');
        }
        imagedestroy($src);

        // Supprimer l'ancien avatar si fourni
        if($oldFilename){
            $this->delete($oldFilename);
        }

        return $newName;
    }

    public function delete(string $fileName): void
    {
        $path = rtrim($this->avatarsDir, '/').'/'.$fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function autorotateIfNeeded(string $path, $img): void
    {
        if (!function_exists('exif_read_data')) {
            return; // EXIF non dispo, ignorer
        }
        $exif = @exif_read_data($path);
        if (!isset($exif['Orientation'])) {
            return;
        }
        $orientation = (int) $exif['Orientation'];
        switch ($orientation) {
            case 3: // 180°
                $this->rotateInPlace($img, 180);
                break;
            case 6: // 90° CW
                $this->rotateInPlace($img, -90);
                break;
            case 8: // 90° CCW
                $this->rotateInPlace($img, 90);
                break;
            default:
                // ok
        }
    }

    /** Rotation en place (GD ne permet pas direct, on recrée la ressource) */
    private function rotateInPlace(&$img, int $angle): void
    {
        $rotated = imagerotate($img, $angle, 0);
        if ($rotated) {
            imagedestroy($img);
            $img = $rotated;
        }
    }
}
