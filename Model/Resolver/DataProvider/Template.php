<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory as TemplateCollection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Page Builder Template data provider
 */
class Template
{
    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @param TemplateCollection $templateCollection
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        TemplateCollection $templateCollection
    ) {
        $this->templateCollection = $templateCollection;
    }

    /**
     * Get template by name
     *
     * @param string $templateName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByTemplateName(string $templateName): array
    {
        $templateData = $this->fetchTemplateData($templateName, TemplateInterface::KEY_NAME);

        return $templateData;
    }

    /**
     * Get template by id
     *
     * @param int $templateId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByTemplateId(int $templateID): array
    {
        $templateData = $this->fetchTemplateData($templateID, TemplateInterface::KEY_ID);

        return $templateData;
    }

    /**
     * Fetch template data by field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchTemplateData($identifier, string $field): array
    {
        $template = $this->templateCollection->create()
        ->addFieldToFilter($field, ['eq' => $identifier])->getFirstItem();
        
        if (empty($template)) {
            throw new NoSuchEntityException(
                __('The template with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }

        return [
            'name' => $template->getName(),
            'created_for' => $template->getCreatedFor(),
            'preview_image' => str_replace('.template-manager/', '', $template->getPreviewImage()),
            'content' => $template->getTemplate()
        ];
    }
}
