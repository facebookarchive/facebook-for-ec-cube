<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

namespace Plugin\FacebookAdsExtention\Controller;


use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Service\CsvExportService;
use Eccube\Twig\Extension\EccubeExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Asset\Packages;

class ProductFeedController extends AbstractController {

  /**
   * @var CsvExportService
   */
  protected $csvExportService;

  /**
   * ProductFeedController constructor.
   *
   * @param CsvExportService $csvExportService
   */
  public function __construct(
      CsvExportService $csvExportService) {
      $this->csvExportService = $csvExportService;
  }
   /**
    * @Route("/product_feed.tsv", name="plugin_FacebookAdsToolbox_feed")
    */
  public function export(Application $app, Request $request, Packages $packages) {
      set_time_limit(0);

      $em = $this->entityManager;
      $em->getConfiguration()->setSQLLogger(null);

      $response = new StreamedResponse();
      $response->setCallback(function () use ($app, $request, $packages) {

        $this->csvExportService->initCsvType(
          CsvType::CSV_TYPE_PRODUCT);

        $this->csvExportService->setConfig(
          array(
            'eccube_csv_export_encoding' => 'UTF-8',
            'eccube_csv_export_separator' => "\t",
            'eccube_csv_export_multidata_separator' => ','
        ));

        $headers = array(
          'id',
          'item_group_id',
          'title',
          'description',
          'google_product_category',
          'link',
          'image_link',
          'additional_image_link',
          'condition',
          'availability',
          'price',
          'brand',
        );
        $this->csvExportService->fopen();
        $this->csvExportService->fputcsv($headers);
        $this->csvExportService->fclose();

        $qb = $this->csvExportService->getProductQueryBuilder($request);

        $qb->resetDQLPart('select')
            ->resetDQLPart('orderBy')
            ->select('p')
            ->andWhere('p.Status = 1')
            ->orderBy('p.update_date', 'DESC')
            ->setMaxResults(20000)
            ->distinct();

        $query = $qb->getQuery();

        // populate base url for image.
        $site_root = $request->getSchemeAndHttpHost();

        $this->csvExportService->fopen();
        foreach ($query->getResult() as $Product) {

          // change availability based on status.
          $availability = 'out of stock';
          if ($Product->getStockFind()) {
            $availability = 'in stock';
          }

          $description = str_replace(PHP_EOL, '', $Product->getDescriptionDetail());
          if (empty($description)) {
            $description = $Product->getName();
          }

          $additional_image_links = '';
          if (count($Product->getProductImage()) > 1) {
            $additional_image_link_list = array();
            foreach ($Product->getProductImage() as $idx => $image) {
              if ($idx < 10) {
                $additional_image_link_list[] = $site_root.$packages->getUrl($image, 'save_image');
              }
            }
            $additional_image_links = implode(',', $additional_image_link_list);
          }

          $row = array(
            $Product->getId(),
            $Product->getId(),
            substr(strip_tags($Product->getName()), 0, 100),
            // description in single line
            substr(strip_tags($description), 0, 1000),
            'EC-Cube', // category
            $request->getSchemeAndHttpHost().
            $this->generateUrl(
              'product_detail',
              array('id' => $Product->getId())), // link
            $site_root.$packages->getUrl($Product->getMainListImage(), 'save_image'), // image_link
            substr($additional_image_links, 0, 2000), // additional_image_link
            'new', // Conditions
            $availability, // availability
            $Product->getPrice02IncTaxMin(), // price
            strip_tags($Product->getName()), // brand
          );
          $this->csvExportService->fputcsv($row);

          $this->entityManager->detach($Product);
          $this->entityManager->clear();
          $query->free();
          flush();
        }
        $this->csvExportService->fclose();
    });

    $response->send();

    return $response;
  }
}
