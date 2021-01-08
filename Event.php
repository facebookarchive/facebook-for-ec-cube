<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

namespace Plugin\FacebookAdsExtention;
use Eccube\Common\Constant;
use Eccube\Event\TemplateEvent;
use Eccube\Entity\Product;
use Eccube\Entity\Order;
use Eccube\Entity\ProductOrderItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Plugin\FacebookAdsExtention\Form\Type\Admin\ConfigType;
use Plugin\FacebookAdsExtention\Repository\ConfigRepository;

class Event implements EventSubscriberInterface
{
  /**
   * @var ConfigRepository
   */
  protected $configRepository;

  /**
   * @var ProductRepository
   */
  protected $productRepository;

  /**
   * ConfigController constructor.
   *
   * @param ConfigRepository $configRepository
   */
  public function __construct(ConfigRepository $configRepository)
  {
      $this->configRepository = $configRepository;
  }
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'trackViewContent',
            'Cart/index.twig' => 'trackAddToCart',
            'Shopping/complete.twig' => 'trackPurchase',
        ];
    }

    protected function getPixelId()
    {
      $Config = $this->configRepository->get();
      return $Config ? $Config->getFbPixel() : null;
    }

    protected function getAgent()
    {
      $eccube_version = str_replace('-', '', Constant::VERSION);
      return 'execcube-'.$eccube_version.'-1.0.0';
    }

    public function trackViewContent(\Eccube\Event\TemplateEvent $event)
    {
      $event->setParameter('fb_pixel_id', $this->getPixelId());
      $event->setParameter('fb_pixel_agent', $this->getAgent());
      $twig_base = '@FacebookAdsExtention/facebook_pixel.twig';
      $event->addAsset($twig_base);

      $twig_fire = '@FacebookAdsExtention/pixel_fire.twig';
      $params = $event->getParameters();
      $product = $params['Product'];
      $product->getProductClasses();
      $event->setParameter('fb_pixel_event', 'ViewContent');
      $event->setParameter('fb_content_ids', [$product->getId()]);
      $event->setParameter('fb_value', $product->getPrice02IncTaxMin());
      $event->addSnippet($twig_fire);
    }

    public function trackAddToCart(\Eccube\Event\TemplateEvent $event)
    {
      $event->setParameter('fb_pixel_id', $this->getPixelId());
      $event->setParameter('fb_pixel_agent', $this->getAgent());
      $twig_base = '@FacebookAdsExtention/facebook_pixel.twig';
      $event->addAsset($twig_base);

      $twig_fire = '@FacebookAdsExtention/pixel_fire.twig';
      $params = $event->getParameters();
      $carts = $params['Carts'];
      $product_ids = array();
      $last_product_id = '';
      $last_product_price = 0;
      foreach ($carts as $cart) {
        foreach ($cart->getCartItems() as $item) {
          $last_product_id = $item->getProductClass()->getProduct()->getId();
          $last_product_price = $item->getPrice();
        }
      }
      $event->setParameter('fb_pixel_event', 'AddToCart');
      $event->setParameter('fb_content_ids', [$last_product_id]);
      $event->setParameter('fb_value', $last_product_price);
      $event->addSnippet($twig_fire);
    }

    public function trackPurchase(\Eccube\Event\TemplateEvent $event)
    {
      $event->setParameter('fb_pixel_id', $this->getPixelId());
      $event->setParameter('fb_pixel_agent', $this->getAgent());
      $twig_base = '@FacebookAdsExtention/facebook_pixel.twig';
      $event->addAsset($twig_base);

      $twig_fire = '@FacebookAdsExtention/pixel_fire.twig';
      $params = $event->getParameters();
      $target_order = $params['Order'];
      $product_ids = [];
      foreach ($target_order->getProductOrderItems() as $productOrderItem) {
        $product_ids[] = $productOrderItem->getProduct()->getId();
      }
      $event->setParameter('fb_pixel_event', 'Purchase');
      $event->setParameter('fb_content_ids', $product_ids);
      $event->setParameter('fb_value', $target_order->getPaymentTotal());
      $event->addSnippet($twig_fire);
    }
}
