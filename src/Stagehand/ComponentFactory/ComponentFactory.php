<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2012 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_ComponentFactory
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\ComponentFactory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @package    Stagehand_ComponentFactory
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ComponentFactory implements IComponentFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $definitions = array();

    /**
     * @var array
     */
    protected $aliases = array();

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->prepareSyntheticServices();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function create($componentID)
    {
        return $this->container->get($this->resolveComponentID($componentID));
    }

    public function set($componentID, $component)
    {
        $serviceID = $this->resolveComponentID($componentID);

        if ($this->container instanceof ContainerBuilder) {
            if ($this->container->hasDefinition($serviceID)) {
                $this->definitions[$serviceID] = $this->container->getDefinition($serviceID);
            }
            if ($this->container->hasAlias($serviceID)) {
                $this->aliases[$serviceID] = $this->container->getAlias($serviceID);
            }
        }

        $this->container->set($serviceID, $component);
    }

    public function clearComponents()
    {
        if ($this->container instanceof ContainerBuilder) {
            foreach ($this->definitions as $id => $definition) {
                $this->container->setDefinition($id, $definition);
            }
            foreach ($this->aliases as $alias => $id) {
                $this->container->setAlias($alias, $id);
            }

            $this->definitions = array();
            $this->aliases = array();
        }

        $containerClass = new \ReflectionObject($this->container);
        foreach (array('services', 'scopedServices', 'loading') as $clearingPropertyName) {
            $clearingProperty = $containerClass->getProperty($clearingPropertyName);
            $clearingProperty->setAccessible(true);
            $clearingProperty->setValue($this->container, array());
            $clearingProperty->setAccessible(false);
        }

        $this->prepareSyntheticServices();
    }

    /**
     * @param string $componentID
     * @return string
     */
    protected function resolveComponentID($componentID)
    {
        return $componentID;
    }

    protected function prepareSyntheticServices()
    {
        $this->container->set('service_container', $this->container);
        $this->set(IComponentFactory::SERVICE_ID, $this);
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
