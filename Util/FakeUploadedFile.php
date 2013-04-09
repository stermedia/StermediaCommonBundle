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

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Fake Uploaded File
 * Like Symfony UploadedFile, but copies the file instead of moving in move().
 *
 * @author     Michalis Kamburelis <m.kamburelis@stermedia.pl>
 * @author     Jakub Paszkiewicz   <j.paszkiewicz@stermedia.pl>
 */
class FakeUploadedFile extends UploadedFile
{
    /**
    * Move file
    *
    * @param string $directory directory
    * @param null   $name      [default=null] name
    *
    * @return \Symfony\Component\HttpFoundation\File\File|void
    */
    public function move($directory, $name = null)
    {
        FileUtil::copy($directory, $name, $this->getPathname());
    }
}