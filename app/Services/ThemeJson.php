<?php

namespace App\Services;

class ThemeJson
{
     $themeData;

    public function __construct()
    {
        // Initialize the theme data
        $this->themeData = [];
    }

    public function setThemeData($data)
    {
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
        // $settings['layout']['contentSize'] = $data['layout']['content-size']['value'] . "px";
        // $settings['layout']['wideSize'] = $data['layout']['wide-size']['value'] . "px";

        // // Color
        // $settings['color']['palette'] = $this->createColorPalette($data['palette']);
        // $settings['color']['defaultPalette'] = false;
        // $settings['color']['defaultGradients'] = false;
        // $settings['color']['defaultDuotone'] = false;

        // // Spacing
        // $settings['spacing'] = $this->createSpacingConfig($data['spacing']);

        // // Elements
        // $this->themeData['styles']['elements'] = $this->get_elements_styles();

        // // Blocks
        // $this->themeData['styles']['blocks'] = $this->get_block_styles();

        return $this->themeData;
    }
}
