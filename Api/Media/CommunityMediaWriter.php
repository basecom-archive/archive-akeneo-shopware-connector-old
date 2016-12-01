<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Api\Media;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class CommunityMediaWriter
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var FileInfoRepositoryInterface
     */
    protected $fileInfoRepository;

    protected $rootDir;

    /**
     * CommunityMediaWriter constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileInfoRepositoryInterface $fileInfoRepository
     * @param $rootDir
     */
    public function __construct(EntityManagerInterface $entityManager, FileInfoRepositoryInterface $fileInfoRepository, $rootDir)
    {

        $this->entityManager = $entityManager;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->rootDir = $rootDir;
    }

    /**
     * @param $value ProductValueInterface
     * @param $apiClient ApiClient
     * @return array|bool
     * @internal param string $rootDir
     */
    public function sendMedia($value, $apiClient, $item)
    {
        if(!$value->getMedia()) return $item;
        $fileInfo = $this->fileInfoRepository->find($value->getMedia());
        if ($fileInfo) {
            $path = $this->rootDir . "/file_storage/catalog/" . $value->getMedia()->getKey();
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $mediaId = $fileInfo->getSwMediaId();
            if(!$mediaId) {
                $mediaArray = [
                    'album' => -1,
                    'file' => $base64,
                    'description' => $value->getMedia()->getOriginalFilename(),
                ];

                $media = $apiClient->post('media/', $mediaArray);
                if (!$media) {
                    return $item;
                }

                $mediaId = $media['data']['id'];

                $fileInfo->setSwMediaId($mediaId);
                $this->entityManager->persist($fileInfo);
                $this->entityManager->flush();
            }
            $item['images'][] = ['mediaId' => $mediaId];
        }

        return $item;
    }
}