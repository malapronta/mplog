Malapronta Logger behavior
====================================

[![Latest Stable Version](https://poser.pugx.org/malapronta/mplog/v/stable.png)](https://packagist.org/packages/malapronta/mplog)
[![Total Downloads](https://poser.pugx.org/malapronta/mplog/downloads.png)](https://packagist.org/packages/malapronta/mplog)

Overview
--------

This is a Doctrine2 behavior extension, based on  Gedmo


Install
-------
Add MPLog in your composer.json:

```js
{
    "require": {
        "malapronta/mplog": "dev-master"
    }
}
```

Using
-----
1) Configure your doctrine extensions file

``` yaml
# app/config/doctrine_extensions.yml

services:
    malapronta.listener.eventpersist:
        class: Ota\ServiceBundle\Listener\EventPersistListener
        tags:
            - { name: doctrine.event_listener, event: preUpdate }
            - { name: doctrine.event_listener, event: postFlush }
            
    # KernelRequest listener
    extension.listener:
        class: Ota\ServiceBundle\Listener\DoctrineExtensionListener
        calls:
            - [ setContainer, [ @service_container ] ]
        tags:
            # loggable hooks user username if one is in security context
            - { name: kernel.event_listener, event: kernel.request, method: onKernelRequest }

    # Doctrine Extension listeners to handle behaviors
    malapronta.listener.mplog:
        class: Malapronta\MpLog\MpLoggerListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ @annotation_reader ] ]
```

And import in your config.yml file

``` yaml
imports:
    - { resource: doctrine_extensions.yml }
```

2) Configure your Listener class

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
 
3) Configure your Entity class

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
