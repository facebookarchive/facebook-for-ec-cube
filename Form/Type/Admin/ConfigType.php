<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

namespace Plugin\FacebookAdsExtention\Form\Type\Admin;

use Plugin\FacebookAdsExtention\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
      public function buildForm(FormBuilderInterface $builder, array $options)
      {
        $builder->add(
        'fb_pixel',
        TextType::class,
        array(
          'required' => true,
          // 'mapped' => false,
          'constraints' => array(
            new Assert\Regex("/^[0-9]*$/")
          ),
          // 'data' => $pixel_id
        ));
      $builder->add(
        'merchant_settings',
        TextType::class,
        array(
          'required' => true,
          // 'mapped' => false,
          'constraints' => array(
            new Assert\Regex("/^[0-9]*$/")
          ),
          // 'data' => $merchant_settings_id
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
