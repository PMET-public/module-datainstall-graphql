<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;

use InvalidArgumentException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Store\Model\ScopeInterface;
use Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory as TemplateCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Downloadable\Api\SampleRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\UrlInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Resolver\Export\AllChildCategories;
use Magento\NegotiableQuote\Api\CommentLocatorInterface;

class ImageZipFile implements ResolverInterface
{
    
    # source directories for images from media directory
    protected const PRODUCT_PATH_ON_SERVER = '/catalog/product';
    protected const CMS_PATH_ON_SERVER = '/';
    protected const TEMPLATE_PATH_ON_SERVER = '/';
    protected const LOGO_PATH_ON_SERVER = '/logo/';
    protected const FAVICON_PATH_ON_SERVER=  '/favicon/';
    protected const DOWNLOADABLE_PATH_ON_SERVER = '/downloadable/files/links';
    protected const DOWNLOADABLE_SAMPLE_PATH_ON_SERVER = '/downloadable/files/samples';
    protected const EMAIL_LOGO_PATH_ON_SERVER = '/email/logo/';
    protected const NEGOTIABLE_QUOTE_ATTACHMENT_PATH_ON_SERVER = '/negotiable_quotes_attachment';

    # directories to save images for data pack
    protected const PRODUCT_PATH_DATAPACK = '/media/catalog/product';
    protected const CMS_PATH_DATAPACK = '/media/';
    protected const TEMPLATE_PATH_DATAPACK = '/media/.template-manager';
    protected const LOGO_PATH_DATAPACK = '/media/logo';
    protected const FAVICON_PATH_DATAPACK = '/media/favicon';
    protected const DOWNLOADABLE_PATH_DATAPACK = '/media/downloadable';
    protected const EMAIL_LOGO_PATH_DATAPACK = '/media/email/';
    protected const NEGOTIABLE_QUOTE_ATTACHMENT_PATH_DATAPACK = '/media/negotiable_quotes_attachment';

    protected const IMAGE_PATTERN="/{{media url=([^}]+)}}/";
        
    /** @var Filesystem\Directory\WriteInterface */
    protected $dataPackDirectory;

    /** @var Filesystem\Directory\WriteInterface */
    protected $copyFile;
    
    /** @var Authentication */
    protected $authentication;
    
    /** @var TemplateRepository */
    protected $templateRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var TemplateCollection */
    protected $templateCollection;

    /** @var ProductCollectionFactory */
    protected $productCollectionFactory;

    /** @var LinkRepositoryInterface */
    protected $downloadableLinkRepository;

    /** @var SampleRepositoryInterface */
    protected $sampleLinkRepository;

    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /** @var PageRepositoryInterface */
    protected $pageRepository;

    /** @var BannerCollectionFactory */
    protected $bannerCollectionFactory;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var FileIo */
    protected $fileIo;

    /** @var DirectoryList */
    protected $directoryList;

    /** @var string */
    protected $baseDir;

    /** @var FileDriver */
    protected $fileDriver;

    /** @var StoreRepositoryInterface */
    protected $storeRepository;

    /** @var CommentLocatorInterface */
    protected $commentLocatorInterface;

    /** @var AllChildCategories */
    protected $allChildCategories;
    
    /**
     *
     * @param Authentication $authentication
     * @param Filesystem $filesystem
     * @param TemplateRepository $templateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TemplateCollection $templateCollection
     * @param ProductCollectionFactory $productCollectionFactory
     * @param LinkRepositoryInterface $downloadableLinkRepository
     * @param SampleRepositoryInterface $sampleLinkRepository
     * @param BlockRepositoryInterface $blockRepository
     * @param PageRepositoryInterface $pageRepository
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param FileIo $fileIo
     * @param FileDriver $fileDriver
     * @param DirectoryList $directoryList
     * @param StoreRepositoryInterface $storeRepository
     * @param CommentLocatorInterface $commentLocatorInterface
     * @param AllChildCategories $allChildCategories
     * @return void
     * @throws FileSystemException
     */
    public function __construct(
        Authentication $authentication,
        Filesystem $filesystem,
        TemplateRepository $templateRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TemplateCollection $templateCollection,
        ProductCollectionFactory $productCollectionFactory,
        LinkRepositoryInterface $downloadableLinkRepository,
        SampleRepositoryInterface $sampleLinkRepository,
        BlockRepositoryInterface $blockRepository,
        PageRepositoryInterface $pageRepository,
        BannerCollectionFactory $bannerCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        FileIo $fileIo,
        FileDriver $fileDriver,
        DirectoryList $directoryList,
        StoreRepositoryInterface $storeRepository,
        CommentLocatorInterface $commentLocatorInterface,
        AllChildCategories $allChildCategories
    ) {
        $this->authentication = $authentication;
        $this->dataPackDirectory = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->templateRepository = $templateRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templateCollection = $templateCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->downloadableLinkRepository = $downloadableLinkRepository;
        $this->sampleLinkRepository = $sampleLinkRepository;
        $this->blockRepository = $blockRepository;
        $this->pageRepository = $pageRepository;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->fileIo = $fileIo;
        $this->fileDriver = $fileDriver;
        $this->directoryList = $directoryList;
        $this->storeRepository = $storeRepository;
        $this->commentLocatorInterface = $commentLocatorInterface;
        $this->allChildCategories = $allChildCategories;
    }

    /**
     * Get status of data installer job
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        $storeCode = $context->getExtensionAttributes()->getStore()->getCode();
        $allImages = [];
        //create a unique directory to store the images
        $this->baseDir = '/'. uniqid("data-install-");

        if (empty($args['categoryIds'])) {
            $categoryIds = [];
        } else {
            $categoryIds = explode(',', $args['categoryIds']);
            //phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $categoryIds = array_merge(
                $categoryIds,
                $this->allChildCategories->getAllCategoryIds($categoryIds, $storeId)
            );
            //$filter = ['category_ids'=>implode(',',$categoryIds)];
        }
        if (empty($args['templateIds'])) {
            $templateIds = [];
        } else {
            $templateIds = explode(',', $args['templateIds']);
        }
        if (empty($args['blockIds'])) {
            $blockIds = [];
        } else {
            $blockIds = explode(',', $args['blockIds']);
        }
        if (empty($args['dynamicBlockIds'])) {
            $dynamicBlockIds = [];
        } else {
            $dynamicBlockIds = explode(',', $args['dynamicBlockIds']);
        }
        if (empty($args['pageIds'])) {
            $pageIds = [];
        } else {
            $pageIds = explode(',', $args['pageIds']);
        }

        if (empty($args['negotiableQuoteIds'])) {
            $negotiableQuoteIds = [];
        } else {
            $pageIds = explode(',', $args['negotiableQuoteIds']);
        }
        //copy template thumbnail images
        $templateImages = $this->getTemplateImagesList($templateIds);
        $allImages= array_merge($allImages, $this->copyFiles(
            $templateImages,
            self::TEMPLATE_PATH_ON_SERVER,
            self::TEMPLATE_PATH_DATAPACK,
            $storeCode
        ));
        
        //copy product images
        $productImages = $this->getProductImagesList($categoryIds);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $productImages,
            self::PRODUCT_PATH_ON_SERVER,
            self::PRODUCT_PATH_DATAPACK,
            $storeCode
        ));
        
        //TODO: copy images in the description of categories
        
        //copy files for downloadable products
        $downloadableFiles = $this->getDownloadableFileList($categoryIds);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $downloadableFiles,
            self::DOWNLOADABLE_PATH_ON_SERVER,
            self::DOWNLOADABLE_PATH_DATAPACK,
            $storeCode
        ));
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $downloadableFiles,
            self::DOWNLOADABLE_SAMPLE_PATH_ON_SERVER,
            self::DOWNLOADABLE_PATH_DATAPACK,
            $storeCode
        ));
        
        //copy defined logo and favicon images
        $logoImages = $this->getLogoImagesList($storeId);
        $allImages= array_merge($allImages, $this->copyFiles(
            $logoImages,
            self::LOGO_PATH_ON_SERVER,
            self::LOGO_PATH_DATAPACK,
            $storeCode
        ));

        $faviconImages = $this->getFaviconImagesList($storeId);
        $allImages= array_merge($allImages, $this->copyFiles(
            $faviconImages,
            self::FAVICON_PATH_ON_SERVER,
            self::FAVICON_PATH_DATAPACK,
            $storeCode
        ));

        $emailLogoImages = $this->getEmailLogoImagesList($storeId);
        $allImages= array_merge($allImages, $this->copyFiles(
            $emailLogoImages,
            self::EMAIL_LOGO_PATH_ON_SERVER,
            self::EMAIL_LOGO_PATH_DATAPACK,
            $storeCode
        ));
        
        //copy block, dynamic block and page images
        $blockImages = $this->getBlocksImagesList($blockIds);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $blockImages,
            self::CMS_PATH_ON_SERVER,
            self::CMS_PATH_DATAPACK,
            $storeCode
        ));

        $pageImages = $this->getPageImagesList($pageIds);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $pageImages,
            self::CMS_PATH_ON_SERVER,
            self::CMS_PATH_DATAPACK,
            $storeCode
        ));

        $dynamicBlockImages = $this->getDynamicBlockImagesList($dynamicBlockIds, $storeId);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $dynamicBlockImages,
            self::CMS_PATH_ON_SERVER,
            self::CMS_PATH_DATAPACK,
            $storeCode
        ));

        $negotiableQuoteAttachements = $this->getNegotiableQuoteAttachmentList($negotiableQuoteIds);
        $allImages= array_merge($allImages, $this->copyFilesWithPath(
            $negotiableQuoteAttachements,
            self::NEGOTIABLE_QUOTE_ATTACHMENT_PATH_ON_SERVER,
            self::NEGOTIABLE_QUOTE_ATTACHMENT_PATH_DATAPACK,
            $storeCode
        ));
        
        //copy cms files by path
        if (array_key_exists('cmsDir', $args)) {
            $this->copyCmsFiles(self::CMS_PATH_ON_SERVER.$args['cmsDir'], self::CMS_PATH_DATAPACK.$args['cmsDir']);
        }
        
        $this->zipDatapackImages();
        $this->moveZipFile();
        $this->deleteTempDirectory();
        $downloadUrl = $this->getDownloadUrl($storeCode);
            
        return [
             'zip_file_download' => $downloadUrl,
             'zip_file_server_path' => $this->directoryList->getPath('media').'/tmp'.$this->baseDir.'.zip',
             'all_images' => $allImages,
        ];
    }
   
    /**
     * Get Product Images List
     *
     * @param array $categoryIds
     * @return array
     */
    private function getProductImagesList(array $categoryIds) : array
    {
        $productImages = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $categoryIds]);
        $products = $collection->getItems();
        foreach ($products as $product) {
            $productImages[] = $product->getImage();
            $productImages[] = $product->getSmallImage();
            $productImages[] = $product->getThumbnail();
        }
        return array_unique($productImages);
    }
    
    /**
     * Get downloadable files list
     *
     * @param array $categoryIds
     * @return array
     */
    private function getDownloadableFileList(array $categoryIds) : array
    {
        $downloadableFiles = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $categoryIds]);
        $products = $collection->getItems();

        //* @var ProductInterface $product */
        foreach ($products as $product) {
            $downloadableLinkdetails = $this->downloadableLinkRepository->getLinksByProduct($product);
            foreach ($downloadableLinkdetails as $link) {
                $downloadableFiles[] = $link->getLinkFile();
            }
            $downloadableSampledetails = $this->sampleLinkRepository->getSamplesByProduct($product);
            foreach ($downloadableSampledetails as $sample) {
                $downloadableFiles[] = $sample->getSampleFile();
            }
        }
        return array_unique($downloadableFiles);
    }
    
    /**
     * Get list of template images
     *
     * @param array $templateIds
     * @return array
     * @throws NoSuchEntityException
     */
    private function getTemplateImagesList(array $templateIds) : array
    {
        $templateImages = [];
        $templates = $this->templateCollection->create()
        ->addFieldToFilter(TemplateInterface::KEY_ID, ['in' => $templateIds])->getAllIds();

        foreach ($templates as $template) {
            $template = $this->templateRepository->get($template);
            //getPreviewImage() includes the path to the image e.g. ".template-manager/homegrocery5f63dbc9390c5.jpg"
            $templateImages[] = $template->getPreviewImage();
        }
        return $templateImages;
    }

    /**
     * Get lis of block images
     *
     * @param array $blockIds
     * @return array
     * @throws LocalizedException
     */
    private function getBlocksImagesList(array $blockIds) :array
    {
        $cmsBlocks = [];
        foreach ($blockIds as $blockId) {
            $blockContent = $this->blockRepository->getById($blockId)->getContent();
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $cmsBlocks = array_merge($cmsBlocks, $this->parseContent($blockContent));
        }
    
        return $cmsBlocks;
    }
    /**
     * Get list of page images
     *
     * @param mixed $pageIds
     * @return array
     * @throws LocalizedException
     */
    private function getPageImagesList($pageIds) : array
    {
        $cmsPages = [];
        foreach ($pageIds as $pageId) {
            $pageContent = $this->pageRepository->getById($pageId)->getContent();
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $cmsPages = array_merge($cmsPages, $this->parseContent($pageContent));
        }
        return $cmsPages;
    }

    /**
     * Get list of dynamic block images
     *
     * @param array $dynamicBlockIds
     * @param int $storeId
     * @return array
     */
    private function getDynamicBlockImagesList($dynamicBlockIds, $storeId) :array
    {
        $dynamicBlocks = [];
        $dynamicBlockCollection = $this->bannerCollectionFactory->create();
        $dynamicBlockCollection->addFieldToSelect('*');
        $dynamicBlockCollection->addFieldToFilter('banner_id', ['in' => $dynamicBlockIds]);
        $dynamicBlockCollection->load();
        foreach ($dynamicBlockCollection as $dynamicBlock) {
            $blockContent = $dynamicBlock->getStoreContents();
            foreach ($blockContent as $content) {
                 // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $dynamicBlocks = array_merge($dynamicBlocks, $this->parseContent($content));
            }
        }
        return $dynamicBlocks;
    }

    /**
     * Get list of negotiable quote attachements
     *
     * @param array $negotiableQuoteIds
     * @return array
     */
    private function getNegotiableQuoteAttachmentList($negotiableQuoteIds) :array
    {
        $negotiableQuoteAttachements=[];
        foreach ($negotiableQuoteIds as $negotiableQuoteId) {
            $comments = $this->commentLocatorInterface->getListForQuote($negotiableQuoteId);
            foreach ($comments as $comment) {
                $attachments = $comment->getAttachments();
                foreach ($attachments as $attachment) {
                    $negotiableQuoteAttachements[] = $attachment->getFilePath();
                }
            }
        }
        return $negotiableQuoteAttachements;
    }

    /**
     * Get list of logo images
     *
     * @param string $storeId
     * @return array
     */
    private function getLogoImagesList(string $storeId) : array
    {
        $logoImages = [];
        $logoImages[] = $this->scopeConfig->getValue(
            'design/header/logo_src',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return array_unique($logoImages);
    }

    /**
     * Get list of favicon images
     *
     * @param string $storeId
     * @return array
     */
    private function getFaviconImagesList(string $storeId) : array
    {
        $logoImages = [];
        $logoImages[] = $this->scopeConfig->getValue(
            'design/head/shortcut_icon',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return array_unique($logoImages);
    }

     /**
      * Get list of email logo images
      *
      * @param string $storeId
      * @return array
      */
    private function getEmailLogoImagesList(string $storeId) : array
    {
        $logoImages = [];
        $logoImages[] = $this->scopeConfig->getValue(
            'design/email/logo',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return array_unique($logoImages);
    }

    /**
     * Make directory
     *
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    private function makeDirectory(string $path) : void
    {
        $f=$this->directoryList->getPath('tmp').$path;
        $this->fileIo->mkdir($this->directoryList->getPath('tmp').$path);
    }

    /**
     * Copy CMS files
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @return void
     * @throws FileSystemException
     */
    private function copyCmsFiles(string $sourcePath, string $destinationPath) : void
    {
        $source = $this->directoryList->getPath('media').$sourcePath;
        $destination = $this->directoryList->getPath('tmp').$this->baseDir.$destinationPath;
        $this->makeDirectory($this->baseDir.$destinationPath);
        $this->cprp($source, $destination);
    }

    /**
     * Copy files without path
     *
     * @param array $files
     * @param string $sourcePath
     * @param string $destinationPath
     * @param string $storeCode
     * @return array
     * @throws FileSystemException
     */
    private function copyFiles(array $files, string $sourcePath, string $destinationPath, string $storeCode) : array
    {
        $returnFiles = [];
        foreach ($files as $file) {
            if ($file == null) {
                continue;
            }
            $fileInfo = $this->fileIo->getPathInfo($file);
            $path = $fileInfo["dirname"];
            $fileName = $fileInfo["basename"];
            $this->makeDirectory($this->baseDir.$destinationPath);
            if ($path == ".") {
                $source = $this->directoryList->getPath('media').$sourcePath.$fileName;
            } else {
                $source = $this->directoryList->getPath('media').$sourcePath.$path.'/'.$fileName;
            }
            $destination = $this->directoryList->getPath('tmp').$this->baseDir.$destinationPath.'/'.$fileName;
            
            $this->fileIo->cp($source, $destination);
            $returnFiles[] = [
                'source' => $source,
                'in_datapack' => $destinationPath.'/'.$file,
                'image_url' => $this->getImageUrl($storeCode).ltrim($sourcePath, '/').$file
            ];
        }
        return $returnFiles;
    }
    
    /**
     * Copy files with path
     *
     * @param array $files
     * @param string $sourcePath
     * @param string $destinationPath
     * @param string $storeCode
     * @return array
     * @throws FileSystemException
     */
    private function copyFilesWithPath(
        array $files,
        string $sourcePath,
        string $destinationPath,
        string $storeCode
    ) : array {
        $returnFiles = [];
        foreach ($files as $file) {
            $fileInfo = $this->fileIo->getPathInfo($file);
            $path = $fileInfo["dirname"];
            $fileName = $fileInfo["basename"];
            $this->makeDirectory($this->baseDir.$destinationPath.$path);
            $source = $this->directoryList->getPath('media').$sourcePath.$path.'/'.$fileName;
            $destination = $this->directoryList->getPath('tmp').$this->baseDir.$destinationPath . $path.'/'.$fileName;
            $this->fileIo->cp($source, $destination);
            $returnFiles[] = [
                'source' => $source,
                'in_datapack' => $destinationPath.$file,
                'image_url' => $this->getImageUrl($storeCode).ltrim($sourcePath, '/').$file
            ];
        }
        return $returnFiles;
    }

    /**
     * Zip the data pack images
     *
     * @return void
     * @throws FileSystemException
     */
    private function zipDatapackImages() : void
    {
        $pathdir = $this->directoryList->getPath('tmp').$this->baseDir.'/media';
        //Enter the name to creating zipped directory
        $zipcreated = $this->directoryList->getPath('tmp').$this->baseDir.'.zip';
        $this->zipData($pathdir, $zipcreated);
    }

    /**
     * Move the zip file to the media directory
     *
     * @return void
     * @throws FileSystemException
     */
    private function moveZipFile() : void
    {
        /** make media/tmp directory */
        $this->fileIo->mkdir($this->directoryList->getPath('media').'/tmp');
        $this->fileIo->mv(
            $this->directoryList->getPath('tmp').$this->baseDir.'.zip',
            $this->directoryList->getPath('media').'/tmp'.$this->baseDir.'.zip'
        );
    }

    /**
     * Delete the temporary directory
     *
     * @return void
     * @throws FileSystemException
     * @throws InvalidArgumentException
     */
    private function deleteTempDirectory() : void
    {
        $this->fileIo->rmdir($this->directoryList->getPath('tmp').$this->baseDir, true);
    }

    /**
     * Get the download url
     *
     * @param string $storeCode
     * @return string
     * @throws NoSuchEntityException
     */
    private function getDownloadUrl(string $storeCode) : string
    {
        $currentStore = $this->storeRepository->get($storeCode);
        $mediaUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl.'tmp'.$this->baseDir.'.zip';
    }

    /**
     * Get the download url
     *
     * @param string $storeCode
     * @return string
     * @throws NoSuchEntityException
     */
    private function getImageUrl(string $storeCode) : string
    {
        $currentStore = $this->storeRepository->get($storeCode);
        $mediaUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * Recursive Copy
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function cprp($source, $destination)
    {
        if ($this->fileDriver->isDirectory($source)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $dir=opendir($source);
            while ($file=readdir($dir)) {
                if ($file!="." && $file!="..") {
                    if ($this->fileDriver->isDirectory($source."/".$file)) {
                        if (!$this->fileDriver->isDirectory($destination."/".$file)) {
                            $this->fileDriver->createDirectory($destination."/".$file);
                        }
                        $this->cprp($source."/".$file, $destination."/".$file);
                    } else {
                        $this->fileDriver->copy($source."/".$file, $destination."/".$file);
                    }
                }
            }
            closedir($dir);
        } else {
            $this->fileDriver->copy($source, $destination);
        }
    }

    /**
     * Zip the data
     *
     * @param mixed $directory
     * @param mixed $zipTo
     * @return void
     */
    private function zipData($directory, $zipTo)
    {
        // initialize the ZIP archive
        $zip = new \ZipArchive;
        $zip->open($zipTo, \ZipArchive::CREATE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        // loop through files and add to zip
        foreach ($files as $name => $file) {
            $filePath = $file->getRealPath();
            if ($this->fileDriver->isExists($name) && $this->fileDriver->isFile($name)) {
                $zip->addFile($filePath, str_replace($directory, 'media', $filePath));
            }
        }
        $zip->close();
    }
    /**
     * Parse content for images
     *
     * @param string $content
     * @return array
     */
    private function parseContent($content) : array
    {
        $matches = [];
        preg_match_all(self::IMAGE_PATTERN, $content, $matches);
        $files = [];
        foreach ($matches[1] as $match) {
            $files[] = $match;
            //also copy source of .renditions images
            $files[] = str_replace('.renditions', '', $match);
        }
         return $files;
    }
}
