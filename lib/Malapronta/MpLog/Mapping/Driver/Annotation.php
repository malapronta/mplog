<?php

namespace Malapronta\MpLog\Mapping\Driver;

use Doctrine\ORM\Mapping\ClassMetadata;
use Malapronta\Mapping\Driver\AbstractAnnotationDriver;

/**
 * Annotation
 *
 * @author      Marcos Vinicius Joia Lazarin <marcos.joia [at] malapronta [dot] com [dot] br>
 * @package     Gedmo.MpLogger
 * @since       2012-12-11
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
    * Annotation to define that this object is MpLogger
    */
    const MPLOGGER = 'Malapronta\\Mapping\\Annotation\\MpLogger';

    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        if ($annot = $this->reader->getClassAnnotation($class, self::MPLOGGER)) {
            $config['MpLogger'] = true;
        }
    }
}
