<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

namespace Plugin\FacebookAdsExtention\Controller\Admin;

use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Repository\ProductRepository;
use Eccube\Event\EventArgs;
use Plugin\FacebookAdsExtention\Form\Type\Admin\ConfigType;
use Plugin\FacebookAdsExtention\Repository\ConfigRepository;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends AbstractController
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
     * @param ProductRepository $productRepository
     */
    public function __construct(ConfigRepository $configRepository, ProductRepository $productRepository)
    {
        $this->configRepository = $configRepository;
        $this->ProductRepository = $productRepository;
    }
    /**
     * @Route("/%eccube_admin_route%/facebook_ads_extention/admin_config", name="facebook_ads_extention_admin_config")
     * @Template("@FacebookAdsExtention/admin/config.twig")
     */
    public function index(Request $request, Paginator $paginator)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('Updated Successfully.', 'admin');
            return $this->redirectToRoute('facebook_ads_extention_admin_config');
        }

        return [
            'form' => $form->createView(),
            'fb_pixel' => [
              'fb_pixel' => $Config ? $Config->getFbPixel() : null,
              'merchant_settings' => $Config ? $Config->getMerchantSettings() : null,
            ],
            'totalItemCount' => $this->getTotalItemCount($request, $paginator),
            'eccubeVersion' => Constant::VERSION,
            'phpVersion' => PHP_VERSION,
            'pluginVersion' => null, // PluginManager::VERSION,
        ];
    }
    /**
     * @Route("/%eccube_admin_route%/facebook_ads_extention/update", name="plugin_FacebookAdsToolbox_update")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAjax(Request $request) {
      if (!($request->isXmlHttpRequest() && $this->isTokenValid())) {
        return $this->json(['status' => 'NG'], 400);
      }
      $Config = $this->configRepository->get();
      $id = $request->get('id');
      $type = $request->get('type');
      switch ($type) {
        case 'pixel':
        $Config->setFbPixel($id);
        $this->entityManager->persist($Config);
        $this->entityManager->flush($Config);
        break;
      }
      return $this->json(['status' => 'OK']);
    }
    private function getTotalItemCount(Request $request, Paginator $paginator) {
      // paginator
      $qb = $this->ProductRepository->getQueryBuilderBySearchDataForAdmin(array());
      $qb->andWhere('p.Status = 1');
      $event = new EventArgs(
          array(
              'qb' => $qb,
              'searchData' => array(),
          ),
          $request
      );
      // $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);
      $searchData = $event->getArgument('searchData');
      $pagination = $paginator->paginate(
          $qb,
          1,
          1,
          array('wrap-queries' => true));
      return $pagination->getTotalItemCount();
    }
}
