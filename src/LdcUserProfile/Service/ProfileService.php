<?php
/**
 * LdcUserProfile
 *
 * @link      http://github.com/adamlundrigan/LdcUserProfile for the canonical source repository
 * @copyright Copyright (c) 2014 Adam Lundrigan & Contributors
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LdcUserProfile\Service;

use Zend\Form\FormInterface;
use LdcUserProfile\Extensions\AbstractExtension;
use ZfcUser\Entity\UserInterface;
use LdcUserProfile\Form\PrototypeForm;

class ProfileService
{
    /**
     * @var array<AbstractExtension>
     */
    protected $extensions = array();

    /**
     * @var FormInterface
     */
    protected $formPrototype;

    public function registerExtension(AbstractExtension $e)
    {
        $this->extensions[$e->getName()] = $e;

        return $this;
    }

    public function unregisterExtension($nameOrInstance)
    {
        unset($this->extensions[
            $nameOrInstance instanceof AbstractExtension
                ? $nameOrInstance->getName()
                : (string) $nameOrInstance]
        );

        return $this;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function constructFormForUser(UserInterface $user)
    {
        $form = clone $this->getFormPrototype();
        $entity = clone $form->getObject();

        foreach ( $this->getExtensions() as $name => $ext ) {
            $form->add($ext->getFieldset(), array('name' => $name));
            $form->getInputFilter()->add($ext->getInputFilter(), $name);
            $entity->{$name} = $ext->getObjectForUser($user);
        }

        $form->bind($entity);

        return $form;
    }

    public function save($entity)
    {
        $result = true;
        foreach ( $this->getExtensions() as $name => $ext ) {
            $result = $result && $ext->save($entity);
        }

        return $result;
    }

    public function getFormPrototype()
    {
        if ( is_null($this->formPrototype) ) {
            $this->formPrototype = new PrototypeForm();
        }

        return $this->formPrototype;
    }

    public function setFormPrototype(FormInterface $form)
    {
        $this->formPrototype = $form;

        return $this;
    }

}
