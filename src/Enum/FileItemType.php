<?php

namespace App\Enum;

/**
 * Représente le type d'un fichier dans le système de stockage pour les fichiers de l'association.
 */
enum FileItemType: string
{
    case FILE = 'file';
    case DIRECTORY = 'directory';
}
