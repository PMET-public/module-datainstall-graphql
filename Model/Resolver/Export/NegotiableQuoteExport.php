<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;


use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Api\Data\ItemNoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\ItemNoteRepositoryInterface as ItemNoteRepository;
use Magento\NegotiableQuote\Model\Comment;
use Magento\NegotiableQuote\Model\CommentAttachment;
use Magento\NegotiableQuote\Model\CommentManagementInterface;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;
use Magento\NegotiableQuote\Model\ItemNote\CriteriaBuilder as ItemNoteCriteriaBuilder;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use MagentoEse\DataInstallGraphQl\Model\Resolver\Sales\Creator;

class NegotiableQuoteExport
{

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    protected string $fileName = 'b2b_negotiated_quotes.json';
    protected array $creators = [];

    private ItemNoteCriteriaBuilder $itemNoteCriteriaBuilder;
    private ItemNoteRepository $itemNoteRepository;
    private CommentManagementInterface $commentManagement;
    private HistoryManagementInterface $historyManagement;
    /**
     * @var Creator
     */
    private Creator $creatorModel;
    private ExtensibleDataObjectConverter $dataObjectConverter;
    private ProductRepository $productRepository;
    private SerializerInterface $serializer;

    /**
     * Page constructor
     *
     * @param Creator $creatorModel
     * @param HistoryManagementInterface $historyManagement
     * @param CommentManagementInterface $commentManagement
     * @param ItemNoteRepository $itemNoteRepository
     * @param ItemNoteCriteriaBuilder $itemNoteCriteriaBuilder
     */
    public function __construct(
        SerializerInterface                   $serializer,
        ProductRepository                     $productRepository,
        Creator                               $creatorModel,
        ExtensibleDataObjectConverter         $dataObjectConverter,
        HistoryManagementInterface            $historyManagement,
        CommentManagementInterface            $commentManagement,
        ItemNoteRepository                    $itemNoteRepository,
        ItemNoteCriteriaBuilder               $itemNoteCriteriaBuilder
    ) {
        $this->itemNoteCriteriaBuilder = $itemNoteCriteriaBuilder;
        $this->itemNoteRepository = $itemNoteRepository;
        $this->commentManagement = $commentManagement;
        $this->historyManagement = $historyManagement;
        $this->creatorModel = $creatorModel;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
    }

    /**
     * Generate Data
     *
     * @param AbstractDb|array $collection
     * @return array
     * @throws NoSuchEntityException
     */
    public function generateData($collection, $withModel = false): array
    {
        $header = [
            'store',
            'customer_email',
            'currency',
            'product' => [
                "sku",
                "qty",
                "configurable_options",
                'negotiated_price_type',
                'negotiated_price_value',
                "note" => [
                    'creator_type',
                    'creator_id',
                    'note',
                    'created_at'
                ]
            ],
            'status',
            'quote_name',
            'negotiated_price_type',
            'negotiated_price_value',
            'expiration_period',
            'creator_type',
            'creator_id',
            'base_negotiated_total_price',
            'comment' => [
                'creator_type',
                'creator_id',
                'comment',
                'is_decline',
                'is_draft',
                'created_at'
            ],
            'history' => [
                'is_seller',
                'author_id',
                'log_data',
                'snapshot_data',
                'status',
                'is_draft',
                'created_at'
            ],
            'shipping_method',
            'refund',
            'payment'
        ];

        $result = [];
        foreach ($collection as $quote) {
            /** @var Quote $quote */
            $negotiableQuote = $this->getQuoteExtensionAttributes($quote);
            $data = [
                'store' => $quote->getStore()->getCode(),
                'site_code' => $quote->getStore()->getWebsite()->getCode(),
                'email' => $quote->getCustomerEmail(),
                'currency' => $quote->getBaseCurrencyCode(),
                'status' => $negotiableQuote->getStatus(),
                'name' => $negotiableQuote->getQuoteName(),
                'negotiated_price_type' => $negotiableQuote->getNegotiatedPriceType(),
                'negotiated_price_value' => $negotiableQuote->getNegotiatedPriceValue(),
                'expiration_period' => $negotiableQuote->getExpirationPeriod(),
                'creator_type_id' => $negotiableQuote->getCreatorType(),
                //'creator_type' => $negotiableQuote->getCreatorType(),
                //'creator_id' => $negotiableQuote->getCreatorId(),
                'creator' => $this->creatorModel->retrieveCreatorById(
                    $negotiableQuote->getCreatorType(), $negotiableQuote->getCreatorId(), $quote->getId()
                ),
                'base_negotiated_total_price' => $negotiableQuote->getBaseNegotiatedTotalPrice(),
                'proposed_shipping_amount' => $negotiableQuote->getShippingPrice(),
                'snapshot' => $negotiableQuote->getSnapshot(),
                'uid' => ''
            ];

            $data['shipping_addresses'] = $this->getShippingAddresses($quote);

            if ($quote->getBillingAddress()) {
                $data['billing_address'] = $this->setAddressData($quote->getBillingAddress());
            }

            if ($quote->getPayment()) {
                $data['selected_payment_method'] = [
                    'code' => $quote->getPayment()->getMethod() ?? '',
                    //'method' => $quote->getPayment()->getMethod() ?? '',
                    'po_number' => $quote->getPayment()->getPoNumber(),
                    'additional_data' => json_encode($quote->getPayment()->getAdditionalData())
                ];
            }

            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                /** @var Item|CartItemInterface $quoteItem */

                $negotiableQuoteItem = $quoteItem
                    ->getExtensionAttributes()
                    ->getNegotiableQuoteItem();
                $extensionAttributes = $negotiableQuoteItem->getExtensionAttributes();

                $productModel = $this->productRepository->getById(
                    $quoteItem->getProductId(),
                    false,
                    $quoteItem->getStoreId(),
                    true
                );
                $item = [
                    "sku" => $quoteItem->getSku(),
                    "qty" => $quoteItem->getQty(),
                    "type" => $quoteItem->getProductType(),
                    'negotiated_price_type' => $extensionAttributes->getNegotiatedPriceType(),
                    'negotiated_price_value' => $extensionAttributes->getNegotiatedPriceValue()
                ];

                // Handle configurable product
                if ($quoteItem->getProductType() === Configurable::TYPE_CODE) {
                    $item['parent_sku'] = $productModel->getSku();

                    $selectedOptions = [];
                    /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $childItem */
                    foreach ($quoteItem->getChildren() as $childItem) {
                        $child = $this->productRepository->getById(
                            $childItem->getProductId(),
                            false,
                            $quoteItem->getStoreId(),
                            true
                        );
                        // Get the selected options for this child product
                        $productAttributes = $productModel->getTypeInstance()
                            ->getConfigurableAttributesAsArray($productModel);
                        foreach ($productAttributes as $attribute) {
                            $attrCode = $attribute['attribute_code'];
                            $attrValue = $child->getData($attrCode);
                            $optionValue = $child->getAttributeText($attrCode);
                            $selectedOptions[$attrCode] = $optionValue;
                        }
                    }

                    $item['super_attribute'] = json_encode($selectedOptions);
                }

                $item["note"] = $this->getNotesFromDb($quoteItem);
                $data['product'][] = $item;
            }
            $data['comments'] = $this->getQuoteComments($quote->getId());
            $data['history'] = $this->getQuoteHistory($quote->getId());

            if ($withModel) {
                $data['model'] = $quote;
            }

            $result[] = $data;
        }

        $return["items"] = $result;
        return $return;
    }

    /**
     * @param Quote $quote
     * @return array
     */
    private function getShippingAddresses(Quote $quote): array
    {
        $data = [];
        $shippingAddresses = $quote->getAllShippingAddresses();
        foreach ($shippingAddresses as $address) {
            $addressData = $this->setAddressData($address);

            if ($address->getShippingMethod()) {
                $addressData['shipping_method'] = $address->getShippingMethod();
                $addressData['shipping_amount'] = [
                    'value' => $address->getShippingAmount(),
                    'currency' => $quote->getQuoteCurrencyCode(),
                ];
            }

            $data[] = $addressData;
        }

        return $data;
    }

    /**
     * @param Address $address
     * @return array
     */
    private function setAddressData(Address $address): array
    {
        $addressData = [
            "firstname" => $address->getFirstname() ?? '',
            "lastname" => $address->getLastname() ?? '',
            "city" => $address->getCity() ?? '',
            "postcode" => $address->getPostcode(),
            "telephone" => $address->getTelephone()
        ];

        return array_merge(
            $addressData,
            [
                'country' => [
                    'code' => $address->getCountryId() ?? '',
                    'label' => $address->getCountry() ?? ''
                ],
                'region' => [
                    'code' => $address->getRegionCode() ?? '',
                    'label' => $address->getRegion(),
                    'region_id'=> $address->getRegionId()
                ],
                'street' => $address->getStreet(),
                //"country_id" => $address->getCountryId(),
                //'region_id' => $address->getRegionId(),
            ]
        );
    }

    private function getNotesFromDb(Item $quoteItem): array
    {
        $noteCriteria = $this->itemNoteCriteriaBuilder->getNotesByItemIdCriteria((int)$quoteItem->getItemId());
        $notes = $this->itemNoteRepository->getList($noteCriteria)->getItems();
        $data = [];
        foreach ($notes as $item) {
            $data[] = [
                'creator_type' => $item->getCreatorType(),
                'creator_type_id' => $item->getCreatorType(),
                'creator_id' => $item->getCreatorId(),
                'creator' => $this->creatorModel->retrieveCreatorById(
                    $item->getCreatorType(), $item->getCreatorId(), $quoteItem->getQuoteId()
                ),
                'note' => $item->getNote(),
                'note_uid' => $item->getNoteId(),
                'created_at' => $item->getCreatedAt()
            ];
        }

        return $data;
    }

    /**
     * Get negotiable quote extension attributes from quote.
     *
     * @param CartInterface $quote
     * @return NegotiableQuoteInterface|null
     */
    private function getQuoteExtensionAttributes(CartInterface $quote): ?NegotiableQuoteInterface
    {
        $extensionAttributes = null;

        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()) {
            $extensionAttributes = $quote->getExtensionAttributes()->getNegotiableQuote();
        }

        return $extensionAttributes;
    }

    /**
     * Get quote comments.
     *
     * @return array
     */
    public function getQuoteComments($quoteId): array
    {
        $comments = $this->commentManagement->getQuoteComments($quoteId);
        $data = [];
        foreach ($comments as $comment) {
            /** @var $comment Comment */

            $files = $this->getCommentAttachments($comment->getId());

            $data[] = [
                'creator_type' => $comment->getCreatorType(),
                'creator_type_id' => $comment->getCreatorType(),
                //'creator_id' => $comment->getCreatorId(),
                'creator' => $this->creatorModel->retrieveCreatorById(
                    $comment->getCreatorType(), $comment->getCreatorId(), $quoteId
                ),
                'text' => $comment->getComment() ?? '',
                'is_decline' => $comment->getIsDecline(),
                'is_draft' => $comment->getIsDraft(),
                'created_at' => $comment->getCreatedAt(),
                'files' => $files,
                'uid' => $comment->getId()
            ];
        }

        return $data;
    }

    protected function getCommentAttachments($commentId): array
    {
        $files = [];
        if ($commentId) {
            $attachments = $this->commentManagement->getCommentAttachments($commentId);
            foreach ($attachments->getItems() as $attachment) {
                /** @var $attachment CommentAttachment */
                $files[] = [
                    'file_name' => $attachment->getFileName(),
                    'file_path' => $attachment->getFilePath(),
                    'file_type' => $attachment->getFileType()
                ];
            }
        }
        return $files;
    }

    public function getQuoteHistory($quoteId): array
    {
        $data = [];
        $history = $this->historyManagement->getQuoteHistory($quoteId);
        foreach ($history as $historyLog) {
            /** @var $historyLog HistoryInterface */
            $isSeller = $historyLog->getIsSeller();
            $author =  ($historyLog->getAuthorId()) ? $this->creatorModel->retrieveCreatorById(
                ($isSeller ? ItemNoteInterface::CREATOR_TYPE_SELLER : ItemNoteInterface::CREATOR_TYPE_BUYER),
                $historyLog->getAuthorId(),
                $quoteId
            ) : 'System';
            $data[] = [
                'is_seller' => $isSeller,
                //'author_id' => $historyLog->getAuthorId(),
                'creator' => $author,
                'author' =>  [
                    'firstname' => '',
                    'lastname' => ''
                ],
                'log_data' => $this->convertOptionsAttributesLog($historyLog->getLogData()),
                'snapshot_data' => $this->convertOptionsAttributesSnap($historyLog->getSnapshotData()),
                'status' => $historyLog->getStatus(),
                'is_draft' =>  $historyLog->getIsDraft(),
                'created_at' => $historyLog->getCreatedAt(),
                'uid' => ''
            ];
        }

        return $data;
    }

    private function convertOptionsAttributesLog($logData) {
        if (!empty($logData)) {
            $log = $this->serializer->unserialize($logData);

            //added to cart has bug in current b2b1.4

            if (isset($log['updated_in_cart'])) {
                foreach ($log['updated_in_cart'] as $sku => &$itemData) {
                    try {
                        $productModel = $this->productRepository->getById($itemData['product_id']);
                        if (isset($itemData['options_changed'])) {
                            $optionChanged = $itemData['options_changed'];
                            // Get the selected options for this child product
                            $productAttributes = $productModel->getTypeInstance()
                                ->getConfigurableAttributesAsArray($productModel);
                            foreach ($optionChanged as $optionId => $optionVals) {
                                if (array_key_exists($optionId, $productAttributes)) {
                                    $attribute = $productAttributes[$optionId];
                                    $attrCode = $attribute['attribute_code'];
                                    $attrOptions = $attribute['options'];
                                    foreach ($attrOptions as $attrOption) {
                                        if ($attrOption['value'] === $optionVals['old_value']) {
                                            $optionVals['old_value'] = $attrOption['label'];
                                        }
                                        if ($attrOption['value'] === $optionVals['new_value']) {
                                            $optionVals['new_value'] = $attrOption['label'];
                                        }
                                    }

                                    unset($itemData['options_changed'][$optionId]);
                                    $itemData['options_changed'][$attrCode] = $optionVals;
                                }
                            }
                        }
                        $itemData['product_sku'] = $productModel->getSku();
                    } catch (NoSuchEntityException $e) {
                    }
                }
            }

            $logData = $this->serializer->serialize($log);
        }

        return $logData;
    }

    private function convertOptionsAttributesSnap($logData) {
        if (!empty($logData)) {
            $log = $this->serializer->unserialize($logData);

            //added to cart has bug in current b2b1.4

            if (isset($log['cart'])) {
                foreach ($log['cart'] as $itemId => &$itemData) {
                    if (!empty($itemData['options']) && isset($itemData['product_id'])) {
                        try {
                            $productModel = $this->productRepository->getById($itemData['product_id']);

                            // Get the selected options for this child product
                            $productAttributes = $productModel->getTypeInstance()
                                ->getConfigurableAttributesAsArray($productModel);
                            foreach ($itemData['options'] as $key => $option) {
                                if (array_key_exists($option['option'], $productAttributes)) {
                                    $attOptionsIndexed = array_column($productAttributes[$option['option']]['options'], 'label', 'value');
                                    if (array_key_exists($option['value'], $attOptionsIndexed)) {
                                        $itemData['options'][$key] = [
                                            'option' => $productAttributes[$option['option']]['attribute_code'],
                                            'value' => $attOptionsIndexed[$option['value']]
                                        ];
                                    }
                                }
                            }
                            $itemData['product_sku'] = $productModel->getSku();
                        } catch (NoSuchEntityException $e) {
                            print_r($e->getMessage());die();
                        }
                    }
                }
            }

            $logData = $this->serializer->serialize($log);
        }

        return $logData;
    }
}
