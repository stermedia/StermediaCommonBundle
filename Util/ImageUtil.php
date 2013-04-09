<?php

/*
 * This file is part of the Stermedia\StermediaCommonBundle
 *
 * (c) Stermedia <http://stermedia.eu>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Stermedia\Bundle\CommonBundle\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Image utilities. Static methods shared by various bundles.
 *
 * @author     Michalis Kamburelis  <m.kamburelis@stermedia.pl>
 * @author     Jakub    Paszkeiwicz <j.paszkiewicz@stermedia.pl>
 */
class ImageUtil
{
    const JPEG_QUALITY=90;

    /**
     * Create thumbnail image from $source to $target.
     * Uses php-gd.
     *
     * @param string  $source           source
     * @param string  $target           target
     * @param string  $extension        contains the extension (without leading dot).
     *                                  By default, both source and target have the same extension (format),
     *                                  but you can also pass non-null value to $targetExtension to override that
     *                                  (in that case, $extension determines only the source format, and $targetExtension is for target format).
     * @param int     $maxSize          is the maximum width/height(the other dimension will be adjusted proportionally).
     * @param boolean $scaleWhenSmaller Should we scale the image up if both it's sizes are smaller than $maxSize.
     * @param boolean $cover            Should we scale the image up to cover whole $maxSize area.
     * @param string  $targetExtension  target extension
     *
     * @static
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException In case of trouble. The upload() methods
     *   of entities can just let this be raised to the outside
     *   (it's guaranteed to correctly prevent persisting the entity to db).
     */
    public static function makeThumbnail($source, $target, $extension, $maxSize, $scaleWhenSmaller = true, $cover = false, $targetExtension = null)
    {
        ImageUtil::resizeImage($source, $target, $extension, $maxSize, $maxSize, $targetExtension, $scaleWhenSmaller, $cover);
    }

    /**
     * Resize image from $source to $target. Uses php-gd.
     *
     * @param string  $source           source
     * @param string  $target           target
     * @param string  $extension        contains the extension (without leading dot).
     *                                  By default, both source and target have the same extension (format),
     *                                  but you can also pass non-null value to $targetExtension to override that
     *                                  (in that case, $extension determines only the source format, and $targetExtension is for target format).
     * @param int     $maxWidth         is the maximum width(the other dimension will be adjusted proportionally).
     * @param int     $maxHeight        is the maximum height(the other dimension will be adjusted proportionally).
     * @param string  $targetExtension  target extension
     * @param boolean $scaleWhenSmaller Should we scale the image up if both it's sizes are smaller than $maxWidth and $maxHeight.
     * @param boolean $cover            Should we scale the image up to cover whole $maxWidth x $maxHeight area.
     *
     * @static
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException In case of trouble. The upload() methods
     *   of entities can just let this be raised to the outside
     *   (it's guaranteed to correctly prevent persisting the entity to db).
     */
    public static function resizeImage($source, $target, $extension, $maxWidth, $maxHeight, $targetExtension = 'jpeg' , $scaleWhenSmaller = false, $cover = true)
    {
        $src=self::imageCreate($source, $extension);

        $sourceWidth = imagesx($src);
        $sourceHeight = imagesy($src);
        $sourceAspectRatio = $sourceWidth/ $sourceHeight;
        $targetAspectRatio = $maxWidth / $maxHeight;
        if ($sourceWidth <= $maxWidth && $sourceHeight <= $maxHeight) {
            if ($scaleWhenSmaller) {
                if ($sourceWidth >= $sourceHeight) {
                    $targetWidth = $maxWidth;
                    $targetHeight = (int) ($maxWidth / $sourceAspectRatio);
                } else {
                    $targetWidth = (int) ($maxHeight * $sourceAspectRatio);
                    $targetHeight = $maxHeight;
                }
            } else {
                $targetWidth = $sourceWidth;
                $targetHeight = $sourceHeight;
            }
        } elseif ($targetAspectRatio > $sourceAspectRatio) {
            $targetWidth = (int) ($maxHeight * $sourceAspectRatio);
            $targetHeight = $maxHeight;
        } else {
            $targetWidth = $maxWidth;
            $targetHeight = (int) ($maxWidth / $sourceAspectRatio);
        }
        if ($cover) {
            if ($targetWidth<$maxWidth) {
                $targetWidth=$maxWidth;
                $targetHeight=$sourceHeight*$maxWidth/$sourceWidth;
            }
            if ($targetHeight<$maxHeight) {
                $targetHeight=$maxHeight;
                $targetWidth=$sourceWidth*$maxHeight/$sourceHeight;
            }
        }

        $tgt = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!imagecopyresampled($tgt, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight)) {
            throw new FileException('Error resizing image.');
        }

        if ($targetExtension === null) {
            $targetExtension = $extension;
        }

        self::imageOutput($tgt, $target, $targetExtension);

        imagedestroy($src);
        imagedestroy($tgt);
    }

    /**
     * Crop image from $source to $target.
     * Uses php-gd.
     *
     * @param string $source          source
     * @param string $target          target
     * @param string $extension       contains the extension (without leading dot).
     *                                By default, both source and target have the same extension (format),
     *                                but you can also pass non-null value to $targetExtension to override that
     *                                (in that case, $extension determines only the source format, and $targetExtension is for target format).
     * @param int    $targetWidth     is the target width
     * @param int    $targetHeight    is the target height
     * @param int    $cropX           is the crop X coordinates
     * @param int    $cropY           is the crop Y coordinates
     * @param int    $cropWidth       is the crop Width
     * @param int    $cropHeight      is the crop Height
     * @param string $targetExtension target extension
     *
     * @static
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException In case of trouble. The upload() methods
     *   of entities can just let this be raised to the outside
     *   (it's guaranteed to correctly prevent persisting the entity to db).
     */
    public static function cropImage($source, $target, $extension, $targetWidth, $targetHeight, $cropX, $cropY, $cropWidth, $cropHeight, $targetExtension = 'jpeg')
    {
        $src=self::imageCreate($source, $extension);

        $tgt = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!imagecopyresampled($tgt, $src, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight, $cropWidth, $cropHeight)) {
            throw new FileException('Error resizing image.');
        }

        if ($targetExtension === null) {
            $targetExtension = $extension;
        }

        self::imageOutput($tgt, $target, $targetExtension);

        imagedestroy($src);
        imagedestroy($tgt);
    }

    /**
     * Checks if extension is supported
     *
     * @param string $extension extension
     *
     * @static
     *
     * @return boolean
     */
    public static function extensionSupported($extension)
    {
        $supportedExtensions = array (
            'png',
            'jpg',
            'jpeg',
            'gif'
        );

        return in_array($extension, $supportedExtensions);
    }

    /**
     * Create a new image from file or URL
     *
     * @param string $source    Source
     * @param string $extension contains the extension (without leading dot).
     *
     * @static
     *
     * @return resource an image resource identifier on success, false on errors.
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public static function imageCreate($source, $extension)
    {
        if (!self::extensionSupported($extension)) {
            throw new FileException('Unsupported image format ' . $extension);
        }
        $image=null;
        switch ($extension) {
            case 'png':
                $image = imagecreatefrompng($source);
                break;
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'gif':
                $image = imagecreatefromgif($source);
                break;
        }

        return $image;
    }

    /**
     * Output image to file
     *
     * @param string $resource        Resource
     * @param string $target          target
     * @param string $targetExtension Target extension
     *
     * @static
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public static function imageOutput($resource, $target, $targetExtension)
    {
        if (!self::extensionSupported($targetExtension)) {
            throw new FileException('Unsupported image format ' . $targetExtension);
        }
        switch ($targetExtension) {
            case 'png':
                imagepng($resource, $target);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($resource, $target, self::JPEG_QUALITY);
                break;
            case 'gif':
                imagegif($resource, $target);
                break;
        }
    }
}
