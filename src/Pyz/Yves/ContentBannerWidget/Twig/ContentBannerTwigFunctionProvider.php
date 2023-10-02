<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\ContentBannerWidget\Twig;

use Spryker\Client\ContentBanner\Exception\MissingBannerTermException;
use Spryker\Shared\Twig\TwigFunctionProvider;
use SprykerShop\Yves\ContentBannerWidget\Dependency\Client\ContentBannerWidgetToContentBannerClientInterface;
use Twig\Environment;

class ContentBannerTwigFunctionProvider extends TwigFunctionProvider
{
    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_SLIDER = 'slider';

    /**
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_SLIDER_WITHOUT_LINK = 'slider-without-link';

    /**
     * @var string
     */
    protected const TWIG_FUNCTION_NAME_CONTENT_BANNER = 'content_banner';

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
     * @var \SprykerShop\Yves\ContentBannerWidget\Dependency\Client\ContentBannerWidgetToContentBannerClientInterface
     */
    protected $contentBannerClient;

    /**
     * @param \Twig\Environment $twig
     * @param string $localeName
     * @param \SprykerShop\Yves\ContentBannerWidget\Dependency\Client\ContentBannerWidgetToContentBannerClientInterface $contentBannerClient
     */
    public function __construct(
        Environment $twig,
        string $localeName,
        ContentBannerWidgetToContentBannerClientInterface $contentBannerClient,
    ) {
        $this->twig = $twig;
        $this->localeName = $localeName;
        $this->contentBannerClient = $contentBannerClient;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return static::TWIG_FUNCTION_NAME_CONTENT_BANNER;
    }

    /**
     * @return callable
     */
    public function getFunction(): callable
    {
        return function (string $contentKey, string $templateIdentifier): string {
            if (!isset($this->getAvailableTemplates()[$templateIdentifier])) {
                return $this->getMessageBannerWrongTemplate($templateIdentifier);
            }
            try {
                $contentBannerTypeTransfer = $this->contentBannerClient->executeBannerTypeByKey($contentKey, $this->localeName);

                if (!$contentBannerTypeTransfer) {
                    return $this->getMessageBannerNotFound($contentKey);
                }
            } catch (MissingBannerTermException $e) {
                return $this->getMessageBannerWrongType($contentKey);
            }

            return (string)$this->twig->render(
                $this->getAvailableTemplates()[$templateIdentifier],
                ['banner' => $contentBannerTypeTransfer],
            );
        };
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableTemplates(): array
    {
        return [
            static::WIDGET_TEMPLATE_IDENTIFIER_BOTTOM_TITLE => '@ContentBannerWidget/views/banner/banner.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_TOP_TITLE => '@ContentBannerWidget/views/banner-alternative/banner-alternative.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_SLIDER => '@ContentBannerWidget/views/slider/slider.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_SLIDER_WITHOUT_LINK => '@ContentBannerWidget/views/slider-without-link/slider-without-link.twig',
        ];
    }

    /**
     * @param string $contentKey
     *
     * @return string
     */
    protected function getMessageBannerNotFound(string $contentKey): string
    {
        return sprintf('<b>Content Banner with key %s not found.</b>', $contentKey);
    }

    /**
     * @param string $templateIdentifier
     *
     * @return string
     */
    protected function getMessageBannerWrongTemplate(string $templateIdentifier): string
    {
        return sprintf('<b>"%s" is not supported name of template.</b>', $templateIdentifier);
    }

    /**
     * @param string $contentKey
     *
     * @return string
     */
    protected function getMessageBannerWrongType(string $contentKey): string
    {
        return sprintf('<b>Content Banner could not be rendered because the content item with key %s is not an banner.</b>', $contentKey);
    }
}
