<?php

namespace App\Service;

use App\Entity\FileItem;
use App\Util\Sanitizer;
use ZipArchive;

class ZipTreeExporter
{
    public function export(FileItem $root, string $zipPath, int $maxDepth = PHP_INT_MAX): void
    {
        $zip = new ZipArchive();
        if($zip->open($zipPath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Impossible de créer le zip.');
        }

        $visited = [];
        $this->addItem($root, $zip, basePath: '', depth: 0, maxDepth: $maxDepth, visited: $visited);

        $zip->close();
    }

    private function addItem(
        FileItem $item,
        ZipArchive $zip,
        string $basePath,
        int $depth,
        int $maxDepth,
        array &$visited
    ): void {
        // Garde-fou cycles
        $id = $item->getId();
        if($id !== null) {
            if(isset($visited[$id])) return;
            $visited[$id] = true;
        }

        // Normalise le nom
        // Normalement le ?: 'unnamed' est inutile ici dans la base c'est en not null.
        $name = Sanitizer::sanitizeName($item->getName() ?: 'unnamed');

        if($item->isFile()){
            $zipPath = ltrim($basePath.$name, '/');
            // Ajoute depuis le disque
            $abs = $this->absoluteStoragePath($item);
            if(is_file($abs)){
                $zip->addFile($abs, $zipPath);
            }
            return;
        } else if($item->isFolder()) { // Je refait le test ici pour futureproof si je rajoute des autres types de fichiers.
            $dirPath = ltrim($basePath.$name.'/', '/');
            $zip->addEmptyDir($dirPath);

            if($depth >= $maxDepth) return;

            // Ici le mieux est de précharger tous les enfants jusqu'à maxDepth.
            foreach($item->getChildren() as $child) {
                $this->addItem($child, $zip, $dirPath, $depth + 1, $maxDepth, $visited);
            }
        }
    }

    private function absoluteStoragePath(FileItem $item): string
    {
        // adapte à ton projet : storagePath est relatif à var/storage/docs
        return \dirname(__DIR__, 2).'/var/storage/docs/'.$item->getPath();
    }
}
