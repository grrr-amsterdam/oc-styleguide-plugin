<?php namespace Grrr\StyleGuide;

use System\Classes\PluginBase;

/**
 * StyleGuide Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'StyleGuide',
            'description' => 'Provides a component to render a style guide.',
            'author'      => 'Grrr',
            'icon'        => 'icon-paint-brush',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Grrr\StyleGuide\Components\StyleGuide' => 'styleGuide',
        ];
    }

}
