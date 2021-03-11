<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\EventListener;

use Contao\Frontend;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\Template;
use Twig\Environment as TwigEnvironment;

/**
 * Uses twig template from theme's namespace if existent.
 */
class RenderingForwarder
{
    private const TWIG_TEMPLATE = 'twig_template';
    private const TEMPLATE_CONTEXT = 'context';
    private const CONTAO_TEMPLATE = 'contao_template';

    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Template $contaoTemplate): void
    {
        // When in frontend, check for theme's template folder
        if (!$contaoTemplate instanceof FrontendTemplate) {
            return;
        }

        /** @var PageModel $pageModel */
        $pageModel = $GLOBALS['objPage'];
        if (!$theme = $pageModel->theme) {
            return;
        }

        $originalName = $contaoTemplate->getName();
        $template = sprintf('@%s/%s.html.twig', $theme, $originalName);
        if (!$this->twig->getLoader()->exists($template)) {
            return;
        }

        // delegate to our proxy template that will call render()
        $contaoTemplate->setName('twig_template_proxy');

        $contaoTemplate->setData([
            self::TWIG_TEMPLATE => $template,
            self::TEMPLATE_CONTEXT => $contaoTemplate->getData(),
            self::CONTAO_TEMPLATE => $originalName,
        ]);
    }
}
