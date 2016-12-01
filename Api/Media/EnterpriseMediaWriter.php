<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Api\Media;

class EnterpriseMediaWriter extends CommunityMediaWriter
{
    const imageMimeTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/tiff', 'image/x-icon', 'image/bmp', 'image/svg+xml'];
    /**
     * @param \PimEnterprise\Component\Catalog\Model\ProductValue $value
     * @param \Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient $apiClient
     * @return array|bool
     */
    public function sendMedia($value, $apiClient, $item)
    {
        $assets = $value->getAssets();
        if(0 === $assets->count()) {
            return parent::sendMedia($value, $apiClient, $item);
        }
        /** @var \PimEnterprise\Component\ProductAsset\Model\Asset $asset */
        foreach($assets as $asset) {
            $variations = $asset->getVariations();
            foreach($variations as $variation) {
                $fileInfo = $variation->getSourceFileInfo();
                if($fileInfo) {
                    $mimeType = $fileInfo->getMimeType();
                    $path = $this->rootDir . "/file_storage/asset/" . $fileInfo->getKey();
                    $data = file_get_contents($path);
                    $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($data);
                    $mediaId = $fileInfo->getSwMediaId();
                    if(!$mediaId) {
                        $mediaArray = [
                            'album' => -1,
                            'file' => $base64,
                            'description' => $fileInfo->getOriginalFilename(),
                        ];

                        $media = $apiClient->post('media/', $mediaArray);
                        if (!$media) {
                            continue;
                        }

                        $mediaId = $media['data']['id'];


                        $fileInfo->setSwMediaId($mediaId);
                        $this->entityManager->persist($fileInfo);
                        $this->entityManager->flush();
                    }
                    $item['images'][] = ['mediaId' => $mediaId];
                }
            }
        }

        return $item;
    }
}