<?php
/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\Bundle\CommonBundle\Util;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
* Fake Uploaded File
* Like Symfony UploadedFile, but copies the file instead of moving in move().
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