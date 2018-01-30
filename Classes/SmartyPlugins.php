<?php
namespace Shopware\Plugins\StripePayment\Classes;

use Shopware\Components\DependencyInjection\Container;
use \Enlight_Components_Snippet_Resource as SnippetResource;

/**
 * @copyright Copyright (c) 2017 VIISON GmbH
 */
class SmartyPlugins
{
    /**
     * The tag name of the custom 'stripe_snippet' smarty block.
     */
    const STRIPE_SNIPPET_BLOCK_NAME = 'stripe_snippet';

    /**
     * @var \Shopware_Components_Snippet_Manager $snippetManager
     */
    protected $snippetManager;

    /**
     * @var SnippetResource $snippetResource
     */
    protected $snippetResource;

    /**
     * @var \Enlight_Template_Manager $templateManager;
     */
    protected $templateManager;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->snippetManager = $container->get('snippets');
        $this->templateManager = $container->get('template');
        if ($container->has('snippet_resource')) {
            // Shopware >= 5.2.4
            $this->snippetResource = $container->get('snippet_resource');
        } else {
            // Create a new SnippetResource instance
            $showSnippetPlaceholder = $container->hasParameter('shopware.snippet.showSnippetPlaceholder')
                && $container->getParameter('shopware.snippet.showSnippetPlaceholder');
            $this->snippetResource = new SnippetResource($this->snippetManager, $showSnippetPlaceholder);
        }
    }

    /**
     * Registers a new filter with the loaded 'template' service instance, which handles the custom
     * 'stripe_snippet' blocks.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function register()
    {
        $this->templateManager->registerFilter(\Smarty::FILTER_PRE, array($this, 'filterStripeSnippetBlocks'));
    }

    /**
     * Parses the given $source to find any 'stripe_snippet' blocks and replaces them with their snippet value,
     * which is escaped so it can be used safely in JavaScript single quote strings. That is, all single quotes
     * in the snippet are guaranteed to be escaped when inserted by this filter. The 'stripe_snippet' block can be
     * used just like a normal 's' block, but current supports only two arguments 'name' and 'namespace' as well
     * as the block content as the default value. If no 'namespace' argument is set, the namespace of the given
     * $source is used. Example usage:
     *
     *  {stripe_snippet name=the/name namespace=some/random_namespace}An optional default snippet value{/stripe_snippet}
     *
     * @param string $source
     * @param \Smarty_Internal_Template $template
     * @return string
     */
    public function filterStripeSnippetBlocks($source, \Smarty_Internal_Template $template)
    {
        $ldl = $template->smarty->left_delimiter;
        $ldle = preg_quote($ldl);
        $rdl = $template->smarty->right_delimiter;
        $rdle = preg_quote($rdl);
        $snippetTag = self::STRIPE_SNIPPET_BLOCK_NAME;
        $defaultNamespace = $this->snippetResource->getSnippetNamespace($template->source);

        // Find all 'stripe_snippet' blocks
        $pattern = "/$ldle$snippetTag(\s.+?)?$rdle(.*?)$ldle\/$snippetTag$rdle/msi";
        while (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
            if (count($matches) != 3) {
                continue;
            }
            $blockArgs = $matches[1][0];
            $blockContent = $matches[2][0];

            // Parse the snippet arguments to retrieve the snippet
            $hasNamespaceArg = preg_match('/(.?)(namespace=)(.*?)(?=(\s|$))/', $blockArgs, $namespaceMatches);
            $namespace = ($hasNamespaceArg && !empty($namespaceMatches[3])) ? trim($namespaceMatches[3]) : $defaultNamespace;
            $hasNameArg = preg_match('/(.?)(name=)(.*?)(?=(\s|$))/', $blockArgs, $nameMatches);
            $name = ($hasNameArg && !empty($nameMatches[3])) ? trim($nameMatches[3]) : $blockContent;
            $snippet = $this->snippetManager->getNamespace($namespace)->get($name, $blockContent);
            // Unescape already escaped single quotes
            $snippet = str_replace('\\\'', '\'', $snippet);
            // Escape all single quotes
            $snippet = str_replace('\'', '\\\'', $snippet);

            // Replace the whole match with the snippet value
            $matchingBlock = $matches[0];
            // phpcs:ignore Generic.PHP.ForbiddenFunctions
            $source = substr_replace($source, $snippet, $matchingBlock[1], strlen($matchingBlock[0]));
        }

        return $source;
    }
}
