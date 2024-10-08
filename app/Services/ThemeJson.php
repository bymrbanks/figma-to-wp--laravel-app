<?php

namespace App\Services;

use Illuminate\Support\Str;

class ThemeJson
{
    protected $themeData;
    protected $variables;
    protected $variable_map = array();

    public function __construct($project)
    {
        // Initialize the theme data
        $this->themeData = [];
        $this->variables = $this->ensureArray(json_decode($project->variables, true));
        $this->update_variable_map();
    }
 
    private function ensureArray($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        return is_array($data) ? $data : [];
    }

    public function setThemeData()
    {
        $variables = $this->variables;
        $this->themeData = []; // Assuming this resets or initializes the theme data
        $this->themeData['$schema'] = "https://schemas.wp.org/trunk/theme.json";
        $this->themeData['version'] = 2;
        $this->themeData['settings'] = [];
        $this->themeData['settings']['appearanceTools'] = true;
        $this->themeData['settings']['useRootPaddingAwareAlignments'] = true;

        // Ensure the settings and layout properties exist
        $settings = &$this->themeData['settings'];
        $settings['layout'] = $settings['layout'] ?? [];
        $settings['color'] = $settings['color'] ?? [];
        $settings['spacing'] = $settings['spacing'] ?? [];

        // Layout
        foreach ($variables as $variable) {
            if (isset($variable['name']) && $variable['name'] == 'layout/Content Size') {
                $settings['layout']['contentSize'] = $variable['value'] . "px";
            }
            if (isset($variable['name']) && $variable['name'] == 'layout/Wide Size') {
                $settings['layout']['wideSize'] = $variable['value'] . "px";
            }
        }

        // Color
        $settings['color']['palette'] = $this->getColorSettings();
        $settings['color']['defaultPalette'] = false;
        $settings['color']['defaultGradients'] = false;
        $settings['color']['defaultDuotone'] = false;

        // Spacing
        $settings['spacing'] = $this->getSpacingSettings();

        // Typography
        $this->themeData['styles']['typography'] = $this->getTypographySettings();
        return $this->themeData;
    }

    public function getSchema()
    {
        // Returns the schema URL
    }

    public function getVersion()
    {
        // Returns the version number

    }

    public function getPatterns()
    {
        // Returns an array of pattern slugs
    }

    public function getLayoutSettings()
    {
        // Returns the layout settings
    }

    public function getTypographySettings()
    {

        $typography = array_filter($this->variables, function ($variable) {
            return strpos($variable['name'], 'font/size') !== false;
        });


        $typographySettings = array(
            'fontSizes' => array(),
            'fontFamilies' => array(),
            'fontWeights' => array(),
            'fontStyles' => array(),
        );

        foreach ($typography as $typographySetting) {
            $name_parts = explode('/', $typographySetting['name']);
            $type = Str::slug($name_parts[0]);
            $subtype = isset($name_parts[1]) ? Str::slug($name_parts[1]) : null;
            $slug = isset($name_parts[1]) ? Str::slug($name_parts[2]) : null;
            $value = $typographySetting['value'];

            if ($type == 'font' && $subtype == 'size') {
                $typographySettings['fontSizes'][] = array(
                    'name' => $slug,
                    'slug' => $slug,
                    'size' => $value,
                );
            }

            if ($type == 'font' && $subtype == 'family') {
                $typographySettings['fontFamilies'][] = array(
                    'name' => $slug,
                    'slug' => $slug,
                    'family' => $value,
                );
            }

            if ($type == 'font' && $subtype == 'style') {
                $typographySettings['fontStyles'][] = array(
                    'name' => $slug,
                    'slug' => $slug,
                    'style' => $value,
                );
            }

            if ($type == 'font' && $subtype == 'weight') {
                $typographySettings['fontWeights'][] = array(
                    'name' => $slug,
                    'slug' => $slug,
                    'weight' => $value,
                );
            }
        }

        return  $typographySettings;
    }

    public function getUseRootPaddingAwareAlignments()
    {
        // Returns the useRootPaddingAwareAlignments setting
    }

    public function getStyles()
    {
        // Returns the styles settings
    }

    public function getTemplateParts()
    {
        // Returns an array of template parts
    }

    public function getCustomTemplates()
    {
        // Returns an array of custom templates
    }


    /**
     * Updates the variable map in the options table.
     */
    public function update_variable_map()
    {
        $this->variable_map = [];
        $variables = $this->variables;

        foreach ($variables as $variable) {
            $object = [];

            $id = $variable['id'];
            $value = $variable['name'];

            $name_parts = explode('/', $value);
            $type = Str::slug($name_parts[0]);
            $subtype = isset($name_parts[1]) ? Str::slug($name_parts[1]) : null;
            $slug = isset($name_parts[1]) ? Str::slug($name_parts[1]) : null;
            $value = $variable['value'];

            if ($type == 'spacing') {
                $object['size'] = $value;
            }

            if ($type == 'palette') {
                $object['color'] = $value;
            }

            if ($type == 'layout') {
                $object['size'] = $value;
            }

            if ($type == 'font' && $subtype == 'size') {
                $object['size'] = $value;
            }

            if ($type == 'font' && $subtype == 'family') {
                $object['family'] = $value;
            }

            if ($type == 'font' && $subtype == 'style') {
                $object['weight'] = $value;
            }

            if ($type == 'font') {
                $slug = isset($name_parts[2]) ? Str::slug($name_parts[2]) : null;
                $object['slug'] = $slug;
            } else {
                $object['slug'] = $slug;
            }

            $this->variable_map[$id] = $object;
        }
    }

    /**
     * Creates a color palette based on the variables.
     *
     * @return array The color palette array.
     */
    private function getColorSettings()
    {
        $colors = array_filter($this->variables, function ($variable) {
            return isset($variable['name']) && strpos($variable['name'], 'palette/') !== false;
        });

        $palette = [];
        foreach ($colors as $colorName => $colorDetails) {
            if (!isset($colorDetails['value']) || !is_array($colorDetails['value'])) {
                continue;
            }
            $value = $colorDetails['value'];

            $r = intval(($value['r'] ?? 0) * 255);
            $g = intval(($value['g'] ?? 0) * 255);
            $b = intval(($value['b'] ?? 0) * 255);
            $a = $value['a'] ?? 1;

            $color = sprintf("#%02x%02x%02x%02x", $r, $g, $b, $a * 255);
            $palette[] = array(
                'color' => $color,
                'slug' => Str::slug($colorName),
                'name' => $colorName
            );
        }

        return $palette;
    }
 

    function getSpacingSettings()
    {
        $spacingConfig = array(
            'spacingScale' => array('steps' => 0),
            'spacingSizes' => array(),
            'units' => array("%", "px", "em", "rem", "vh", "vw")
        );

        $spacingSizes = array_filter($this->variables, function ($variable) {
            return isset($variable['name']) && strpos($variable['name'], 'spacing/') !== false;
        });

        foreach ($spacingSizes as $spacingSize) {
            if (!isset($spacingSize['slug']) || !isset($spacingSize['value'])) {
                continue;
            }
            $spacingConfig['spacingSizes'][] = array(
                'name' => $spacingSize['slug'],
                'slug' => $spacingSize['slug'],
                'size' => $this->pxToMinFunction($spacingSize['value']),
            );
        }

        return $spacingConfig;
    }


    private function pxToMinFunction($px, $rootFontSize = 16, $referenceViewportWidth = 1440)
    {
        // Convert pixels to rem (assuming the root font-size is 16px by default)
        $rem = $px / $rootFontSize;

        // Convert pixels to vw based on a reference viewport width
        // 1vw = 1% of viewport width, so $px / (1% of reference viewport width)
        $vw = ($px / ($referenceViewportWidth / 100));

        // Return the min() CSS function string
        return "min(" . $rem . "rem, " . $vw . "vw)";
    }
}
