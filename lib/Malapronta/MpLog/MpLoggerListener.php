<?php

namespace Malapronta\MpLog;

use Doctrine\Common\EventArgs;
use Doctrine\Common\NotifyPropertyChanged;
use Malapronta\Mapping\MappedEventSubscriber;

/**
 * MpLogger listener
 *
 * @author      Marcos Vinicius Joia Lazarin <marcos.joia [at] malapronta [dot] com [dot] br>
 * @package     Malapronta.MpLogger
 * @since       2012-12-11
 */
class MpLoggerListener extends MappedEventSubscriber
{
    /**
     * Security UserID for identification
     * 
     * @var  integer
     */
    protected $updatedByUser;

    /**
     * IP from requested for identification
     * 
     * @var  string
     */
    protected $updatedByIp;

    /**
     * Type for identification
     */
    protected $updatedByType;

    /**
     * We want to subscribe to these two events
     * 
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'loadClassMetadata',
        );
    }

    /**
    * {@inheritDoc}
    */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * Set updatedByUser attribute
     *
     * @param   integer   $updatedByUser
     */
    public function setUpdatedByUser($updatedByUser)
    {
        if (is_integer($updatedByUser)) {
            $this->updatedByUser = $updatedByUser;
        } else {
            throw new \Exception("{MpLoggerListener} updatedByUser must be a integer");
        }
    }

    /**
     * Set updatedByIp attribute
     *
     * @param   string    $updatedByIp
     */
    public function setUpdatedByIp($updatedByIp)
    {
        if (is_string($updatedByIp)) {
            $this->updatedByIp = $updatedByIp;
        } else {
            throw new \Exception("{MpLoggerListener} updatedByIp must be a string");
        }
    }

    /**
     * Set updatedByType attribute
     *
     * @param string $updatedByType
     */
    public function setUpdatedByType($updatedByType)
    {
        if (is_string($updatedByType)) {
            $this->updatedByType = $updatedByType;
        } else {
            throw new \Exception("{MpLoggerListener} updatedByType must be a string");
        }
    }

    /**
     * Looks for MpLogger objects being inserted or updated
     *
     * @param   EventArgs   $args
     * @return  void
     */
    public function onFlush(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();

        //For each object being inserted...
        foreach ($ea->getScheduledObjectInsertions($uow) as $object) {
            $class = get_class($object);
            $meta = $om->getClassMetadata($class);
            if ($config = $this->getConfiguration($om, $meta->name)) {        
                if (!method_exists($object, 'setUpdatedByUser')) {
                    throw new \Exception("function setUpdatedByUser must be implemented on {$class}");
                }
              
                if (!method_exists($object, 'setUpdatedByIp')) {
                    throw new \Exception("function setUpdatedByIp must be implemented on {$class}");
                }
                
                if(!method_exists($object, 'setUpdatedByType')) {
                    throw new \Exception("function setUpdatedByType must be implemented on {$class}");
                }
            
                $object->setUpdatedByUser($this->updatedByUser);
                $object->setUpdatedByIp($this->updatedByIp);
                $object->setUpdatedByType($this->updatedByType);
                
                $om->persist($object);
            }
        }

        //For each object being updated...
        foreach ($ea->getScheduledObjectUpdates($uow) as $object) {
            $class = get_class($object);
            $meta = $om->getClassMetadata($class);
            if ($config = $this->getConfiguration($om, $meta->name)) {
                if (!method_exists($object, 'setUpdatedByUser')) {
                    throw new \Exception("function setUpdatedByUser must be implemented on {$class}");
                }
                  
                if (!method_exists($object, 'setUpdatedByIp')) {
                    throw new \Exception("function setUpdatedByIp must be implemented on {$class}");
                }
                  
                if (!method_exists($object, 'setUpdatedByType')) {
                    throw new \Exception("function setUpdatedByType must be implemented on {$class}");
                }
                
                $object->setUpdatedByUser($this->updatedByUser);
                $object->setUpdatedByIp($this->updatedByIp);
                $object->setUpdatedByType($this->updatedByType);
                $om->persist($object);
            }
        }
    }
}

