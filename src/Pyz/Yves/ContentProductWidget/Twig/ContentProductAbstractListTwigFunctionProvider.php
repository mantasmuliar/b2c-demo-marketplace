<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\ContentProductWidget\Twig;

use Spryker\Client\ContentProduct\Exception\InvalidProductAbstractListTermException;
use Spryker\Shared\Twig\TwigFunctionProvider;
use SprykerShop\Yves\ContentProductWidget\Reader\ContentProductAbstractReaderInterface;
use Twig\Environment;

/**
 * @method \SprykerShop\Yves\ContentProductWidget\ContentProductWidgetFactory getFactory()
 */
class ContentProductAbstractListTwigFunctionProvider extends TwigFunctionProvider
{
    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_SLIDER_WITH_BUTTON = 'slider-with-button';

    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_SLIDER = 'slider';

    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_SLIDER_NOT_INCLUDE_GROUP = 'slider-not-include-group';

    /**
     * @var string
     */
    protected const FUNCTION_CONTENT_PRODUCT_ABSTRACT_LIST = 'content_product_abstract_list';

    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_BOTTOM_TITLE = 'bottom-title';

    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_TOP_TITLE = 'top-title';

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $localeName;

    /**
     * @var \SprykerShop\Yves\ContentProductWidget\Reader\ContentProductAbstractReaderInterface
     */
    protected $contentProductAbstractReader;

    /**
     * @param \Twig\Environment $twig
     * @param string $localeName
     * @param \SprykerShop\Yves\ContentProductWidget\Reader\ContentProductAbstractReaderInterface $contentProductAbstractReader
     */
    public function __construct(
        Environment $twig,
        string $localeName,
        ContentProductAbstractReaderInterface $contentProductAbstractReader,
    ) {
        $this->twig = $twig;
        $this->localeName = $localeName;
        $this->contentProductAbstractReader = $contentProductAbstractReader;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return static::FUNCTION_CONTENT_PRODUCT_ABSTRACT_LIST;
    }

    /**
     * @return callable
     */
    public function getFunction(): callable
    {
        return function (string $contentKey, string $templateIdentifier): string {
            if (!isset($this->getAvailableTemplates()[$templateIdentifier])) {
                return $this->getMessageProductAbstractWrongTemplate($templateIdentifier);
            }

            try {
                $productAbstractViewCollection = $this->contentProductAbstractReader
                    ->findProductAbstractCollection($contentKey, $this->localeName);
            } catch (InvalidProductAbstractListTermException $exception) {
                return $this->getMessageProductAbstractWrongType($contentKey);
            }

            if ($productAbstractViewCollection === []) {
                return $this->getMessageProductAbstractNotFound($contentKey);
            }

            return (string)$this->twig->render(
                $this->getAvailableTemplates()[$templateIdentifier],
                [
                    'productAbstractViewCollection' => $productAbstractViewCollection,
                ],
            );
        };
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableTemplates(): array
    {
        return [
            static::WIDGET_TEMPLATE_IDENTIFIER_BOTTOM_TITLE => '@ContentProductWidget/views/cms-product-abstract-list/cms-product-abstract-list.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_TOP_TITLE => '@ContentProductWidget/views/cms-product-abstract-list-alternative/cms-product-abstract-list-alternative.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_SLIDER_WITH_BUTTON => '@ContentProductWidget/views/cms-product-abstract-and-button/cms-product-abstract-and-button.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_SLIDER => '@ContentProductWidget/views/cms-product-abstract/cms-product-abstract.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_SLIDER_NOT_INCLUDE_GROUP => '@ContentProductWidget/views/cms-product-abstract-not-include-group/cms-product-abstract-not-include-group.twig',
        ];
    }

    /**
     * @param string $contentKey
     *
     * @return string
     */
    protected function getMessageProductAbstractNotFound(string $contentKey): string
    {
        return sprintf('<strong>Content product abstract list with content key "%s" not found.</strong>', $contentKey);
    }

    /**
     * @param string $templateIdentifier
     *
     * @return string
     */
    protected function getMessageProductAbstractWrongTemplate(string $templateIdentifier): string
    {
        return sprintf('<strong>"%s" is not supported name of template.</strong>', $templateIdentifier);
    }

    /**
     * @param string $contentKey
     *
     * @return string
     */
    protected function getMessageProductAbstractWrongType(string $contentKey): string
    {
        return sprintf('<strong>Content product abstract list widget could not be rendered because the content item with key "%s" is not an abstract product list.</strong>', $contentKey);
    }
}
