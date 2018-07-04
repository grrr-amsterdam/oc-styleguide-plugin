<?php
namespace Grrramsterdam\StyleGuide\Classes;
use Garp\Functional as f;
use October\Rain\Halcyon\Processors\SectionParser;
use October\Rain\Parse\Twig as TwigParser;
use Cms\Classes\Theme;

/**
 * @package Grrramsterdam\StyleGuide\Classes
 * @author  Harmen Janssen <harmen@grrr.nl>
 */
class Styleguide
{
    protected $_basePath;

    protected $_validExtension;

    protected $_controller;

    public function __construct(
        string $basePath, \Cms\Classes\Controller $controller, string $validExtension = 'twig'
    ) {
        $this->_basePath = $basePath;
        $this->_controller = $controller;
        $this->_validExtension = $validExtension;
    }

    public function getPartials(): array
    {
        return array_map(
            [$this, '_filenameToMeta'],
            $this->_getFilenames()
        );
    }

    /**
     * Create a metadata object for a given path.
     *
     * @param string $path Absolute path to the partial file.
     * @return array
     */
    protected function _filenameToMeta(string $path = null): array
    {
        $parser = new SectionParser();
        $partial = $parser->parse(file_get_contents($path));
        if (array_key_exists(SectionParser::ERROR_INI, $partial['settings'])) {
            throw new \Exception(
                sprintf('Settings of partial %s are invalid.', basename($path))
            );
        }
        $partial['settings'] = $this->_arrayizeSettings($partial['settings']);
        $partial['settings']['examples'] = $this->_renderExamples(
            f\either(f\prop_in(['settings', 'examples'], $partial), []),
            $path
        );

        return [
            'path' => $path,
            'contents' => f\prop('markup', $partial),
            'params'   => f\prop_in(['settings', 'params'], $partial),
            'examples' => f\prop_in(['settings', 'examples'], $partial),
            'description' => f\prop_in(['settings', 'description'], $partial),
        ];
    }

    protected function _renderExamples(array $examples, string $path): array
    {
        return f\map(
            f\pipe(
                $this->_decodeJson(),
                $this->_renderExample($path)
            ),
            $examples
        );
    }

    protected function _arrayizeSettings(array $settings): array
    {
        return f\reduce_assoc(
            function ($settings, $value, $key) {
                return array_merge_recursive($settings, $this->_settingToArray($key, $value));
            },
            [],
            $settings
        );
    }

    protected function _decodeJson(): \Closure
    {
        return function (string $json): array
        {
            $response = json_decode($json, true);
            if (!$response && json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
            return $response;
        };
    }

    protected function _renderExample(string $path): \Closure {
        $relativePath = str_replace($this->_getPartialDirectory(), '', $path);
        return f\partial([$this->_controller, 'renderPartial'], $relativePath);
    }

    /**
     * @return array
     */
    protected function _getFilenames(): array
    {
        $partials = [];
        $fullPath = $this->_getPartialDirectory() . ltrim($this->_basePath, '/');
        $iterator = new \DirectoryIterator($fullPath);
        foreach ($iterator as $item) {
            if ($this->_isValidTemplate($item)) {
                $partials[] = $item->getPathName();
            }
        }
        return $partials;
    }

    protected function _isValidTemplate(\DirectoryIterator $file): string
    {
        return strpos($file->getFilename(), '.' . $this->_validExtension) !== false;
    }

    protected function _settingToArray(string $arrayLike, $finalValue = []): array
    {
        $parts = explode('.', $arrayLike, 2);
        return [
            $parts[0] => count($parts) === 1 ?
                         $finalValue :
                         $this->_settingToArray($parts[1], $finalValue)
        ];
    }

    protected function _getPartialDirectory(): string
    {
        return Theme::getActiveTheme()->getPath() . '/partials/';
    }
}
