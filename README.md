Malapronta Logger behavior
====================================

Overview
--------

This is a Doctrine2 behavior extension, based on  Gedmo


Using
-----

1) Configure your Listener class

```php

<?php

// src/Your/NameBundle/Listener/DoctrineExtensionListener.php

namespace Your\NameBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineExtensionListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    { 
        $securityContext = $this->container->get('security.context', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if(null !== $securityContext && null !== $securityContext->getToken() && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggable = $this->container->get('malapronta.listener.mplog');
            $loggable->setUpdatedByUser($securityContext->getToken()->getUser()->getId());
            $loggable->setUpdatedByIp($this->getRemoteAddr());
            $loggable->setUpdatedByType('TYPE_NAME');
        }
    }
  
    private function getRemoteAddr()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';
    }
}
``` 
 
2) Configure your Entity class

```php

<?php

// src/Your/NameBundle/Entity/Foo.php

namespace Your\NameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Malapronta\Mapping\Annotation as Malapronta;

/**
 * @ORM\Entity
 * @ORM\Table(name="foo")
 * @Malapronta\MpLogger
 */
class Foo 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /** 
     * @ORM\Column(name="updated_by_user", type="integer") 
     */
    private $updatedByUser;
    
    /** 
     * @ORM\Column(name="updated_by_ip", type="string", length=39) 
     */
    private $updatedByIp;
    
    /** 
     * @ORM\Column(name="updated_by_type", type="string", length=255) 
     */
    private $updatedByType;
    
    // implement get and set methods
}
```
