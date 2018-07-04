<?php namespace Grrramsterdam\StyleGuide\Components;

use Cms\Classes\ComponentBase;
use Grrramsterdam\StyleGuide\Classes\StyleGuide as StyleGuideFormatter;

class StyleGuide extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'StyleGuide Component',
            'description' => 'Renders a style guide.'
        ];
    }

    public function defineProperties()
    {
        return [
            'path' => [
                'title' => 'path',
                'type' => 'string',
                'description' => 'Relative to the theme\'s partials directory.'
            ],
            'extension' => [
                'title' => 'extension',
                'type' => 'string',
                'description' => 'File extension to filter on.',
                'default' => 'htm'
            ]
        ];
    }

    public function onRun()
    {
        $styleGuide = new StyleGuideFormatter(
            $this->property('path'),
            $this->controller,
            $this->property('extension')
        );
        $this->page['partials'] = $styleGuide->getPartials();
    }
}
