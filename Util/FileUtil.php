<?php
/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\CommonBundle\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;



/**
 * File utilities. Static methods shared by various bundles.
 *
 * @package    CommonBundle
 * @subpackage Utilities
 * @author     Michalis Kamburelis <michalis.kambi@gmail.com>
 * @author     Jakub Paszkiewicz   <j.paszkiewicz@stermedia.pl>
 */
class FileUtil
{
    /**
     * Make sure given directory exists and is writable.
     *
     * @param string $directory
     *
     * @static
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException In case of trouble. The upload() methods of entities can just let this be raised to the outside (it's guaranteed to correctly prevent persisting the entity to db).
     */
    public static function makeWritableDirectory($directory)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }
    }

    /**
     * Copy a file to a given directory, creating the directory if necessary.
     * Modeled after Symfony\Component\HttpFoundation\File\File->move method,
     * the directory is created with exactly the same permissions.
     *
     * @param string $directory  The destination directory, without final (back)slash.
     * @param string $name       The name of the destination file, may be null to just take basename($sourcePath). We will always ignore the directory part of this parameter.
     * @param string $sourcePath The path of the source file.Either relative to the current directory or absolute.
     *
     * @static
     *
     * @throws FileException In case of trouble. The upload() methods of entities can just let this be raised to the outside (it's guaranteed to correctly prevent persisting the entity to db)
     */
    public static function copy($directory, $name, $sourcePath)
    {
        static::makeWritableDirectory($directory);

        $target = $directory.DIRECTORY_SEPARATOR.(null === $name ? basename($sourcePath) : basename($name));

        if (!@copy($sourcePath, $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s)', $sourcePath, $target, strip_tags($error['message'])));
        }

        @chmod($target, 0666 & ~umask());
    }

    /**
     * Create an UploadedFile instance faking the upload of given $filename.
     * It is actually a descendant of UploadedFile, hacked to copy instead of move.
     *
     * @param string $path path
     *
     * @static
     *
     * @return FakeUploadedFile
     */
    public static function fakeUploadedFile($path)
    {
        /* guess mime type just like Symfony\Component\HttpFoundation\File\File */
        $guesser = MimeTypeGuesser::getInstance();
        $mime = $guesser->guess($path);

        return new FakeUploadedFile($path, basename($path), $mime, filesize($path));
    }
}
