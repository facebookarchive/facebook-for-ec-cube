<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

namespace Plugin\FacebookAdsExtention\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_facebook_ads_extention_config")
 * @ORM\Entity(repositoryClass="Plugin\FacebookAdsExtention\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_pixel", type="string", length=255)
     */
    private $fb_pixel;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_settings", type="string", length=255)
     */
    private $merchant_settings;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFbPixel()
    {
        return $this->fb_pixel;
    }

    /**
     * @param string $fb_pixel
     *
     * @return $this;
     */
    public function setFbPixel($fb_pixel)
    {
        $this->fb_pixel = $fb_pixel;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantSettings()
    {
        return $this->merchant_settings;
    }

    /**
     * @param string $merchant_settings
     *
     * @return $this;
     */
    public function setMerchantSettings($merchant_settings)
    {
        $this->merchant_settings = $merchant_settings;
        return $this;
    }
}
