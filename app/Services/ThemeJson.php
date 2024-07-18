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
        $this->variables = json_decode($project->variables);
        $this->update_variable_map();
    }

    public function setThemeData()
    {
        $variables = $this->variables;
        $this->themeData = []; // Assuming this resets or initializes the theme data
        $this->themeData['$schema'] = "https://schemas.wp.org/trunk/theme.json";
        $this->themeData['version'] = 2;
        $this->themeData['settings'] = [];
        $this->themeData['settings']['appearanceTools'] = true;

        // Ensure the settings and layout properties exist
        $settings = &$this->themeData['settings'];
        $settings['layout'] = $settings['layout'] ?? [];
        $settings['color'] = $settings['color'] ?? [];
        $settings['spacing'] = $settings['spacing'] ?? [];

        // // Layout
        foreach ($variables as $variable) {
            if ($variable->name == 'layout/Content Size') {
                $settings['layout']['contentSize'] = $variable->value . "px";
            }
            if ($variable->name == 'layout/Wide Size') {
                $settings['layout']['wideSize'] = $variable->value . "px";
            }
        }
        // $settings['layout']['wideSize'] = $data['layout']['wide-size']['value'] . "px";

        // Color
        $settings['color']['palette'] = $this->createColorPalette();
        $settings['color']['defaultPalette'] = false;
        $settings['color']['defaultGradients'] = false;
        $settings['color']['defaultDuotone'] = false;

        // Spacing
        $settings['spacing'] = $this->createSpacingConfig();

        // Elements
        // $this->themeData['styles']['elements'] = $this->get_elements_styles();

        // Blocks
        // $this->themeData['styles']['blocks'] = $this->get_block_styles();

        return $this->themeData;
    }


    /**
     * Updates the variable map in the options table.
     */
    public function update_variable_map()
    {
        $this->variable_map = [];
        // Get the variables from the options

        $variables = $this->variables;

        // Iterate over each variable
        foreach ($variables as $variable) {
            // Get the variable name and value
            $object  = [];

            $id = $variable->id;
            $value = $variable->name;

            $name_parts = explode('/', $value);
            $type = Str::slug($name_parts[0]);
            $subtype = isset($name_parts[1]) ? Str::slug($name_parts[1]) : null;
            $slug = isset($name_parts[1]) ? Str::slug($name_parts[1]) : null;
            $value = $variable->value;


            if ($type == 'spacing') {
                $object['size'] =  $value;
            }

            if ($type == 'palette') {

                $object['color'] = $value;
            }

            if ($type == 'layout') {
                $object['size'] = $value;
            }

            // Add conditions for font size and font family
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


            // Add the variable to the map
            $this->variable_map[$id] = $object;
        }
        $this->variable_map;
    }

    /**
     * Creates a color palette based on the variables.
     *
     * @return array The color palette array.
     */
    private function createColorPalette()
    {
        $colors = array_filter($this->variables, function($variable) {
            return strpos($variable->name, 'palette/') !== false;
        });

        $palette = [];
        foreach ($colors as $colorName => $colorDetails) {
            $value = $colorDetails->value;

            $r = intval($value->r * 255);
            $g = intval($value->g * 255);
            $b = intval($value->b * 255);
            $a = $value->a;

            $color = sprintf("#%02x%02x%02x%02x", $r, $g, $b, $a * 255);
            $palette[] = array(
                'color' => $color,
                'slug' => Str::slug($colorName),
                'name' => $colorName
            );
        }

        return $palette;
    }


    function createSpacingConfig()
    {
        $spacingConfig = array(
            'spacingScale' => array('steps' => 0),
            'spacingSizes' => array(),
            'units' => array("%", "px", "em", "rem", "vh", "vw")
        );

        $spacingSizes = array_filter($this->variables, function($variable) {
            return strpos($variable->name, 'spacing/') !== false;
        });

        foreach ($spacingSizes as $spacingSize) {

            $spacingConfig['spacingSizes'][] = array(
                'name' => $spacingSize->slug,
                'slug' => $spacingSize->slug,
                'size' => $this->pxToMinFunction($spacingSize->value),
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
