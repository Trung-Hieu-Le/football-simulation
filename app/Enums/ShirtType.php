<?php

namespace App\Enums;

enum ShirtType: string
{
    case SOLID = 'solid';
    case SLEEVES = 'sleeves';
    case COLLAR = 'collar';
    case GRADIENT = 'gradient';
    case RADIAL_GRADIENT = 'radial_gradient';
    case HEAT_GRADIENT = 'heat_gradient';
    case HALF_VERTICAL = 'half_vertical';
    case HALF_HORIZONTAL = 'half_horizontal';
    case DIAGONAL = 'diagonal';
    case LINE_VERTICAL = 'line_vertical';
    case LINE_HORIZONTAL = 'line_horizontal';
    case SASH = 'sash';
    case STRIPES_VERTICAL = 'stripes_vertical';
    case STRIPES_HORIZONTAL = 'stripes_horizontal';
    case STRIPES_DIAGONAL = 'stripes_diagonal';
    case CHECKERED = 'checkered';
    case DIAMOND = 'diamond';
    case CROSS_FINE = 'cross_fine';
    case DOTS_TINY = 'dots_tiny';
    case DOTS_OFFSET = 'dots_offset';
    case SERRATED = 'serrated';
    case FLOWER = 'flower';
    case WAVE = 'wave';
    case ZIGZAG = 'zigzag';

    public function label(): string
    {
        return match ($this) {
            // Solid & Gradient
            self::SOLID => 'Solid',
            self::SLEEVES => 'Sleeves',
            self::COLLAR => 'Collar',
            self::GRADIENT => 'Gradient',
            self::RADIAL_GRADIENT => 'Radial Gradient',
            self::HEAT_GRADIENT => 'Heat Gradient',

            // Half Patterns
            self::HALF_VERTICAL => 'Vertical Half',
            self::HALF_HORIZONTAL => 'Horizontal Half',
            self::DIAGONAL => 'Diagonal',

            // Stripes
            self::LINE_VERTICAL => 'Vertical Line',
            self::LINE_HORIZONTAL => 'Horizontal Line',
            self::SASH => 'Sash',
            self::STRIPES_VERTICAL => 'Vertical Stripes',
            self::STRIPES_HORIZONTAL => 'Horizontal Stripes',
            self::STRIPES_DIAGONAL => 'Diagonal Stripes',

            // Dots & Grid
            self::CHECKERED => 'Checkered',
            self::DIAMOND => 'Diamond',
            self::CROSS_FINE => 'Fine Cross',
            self::DOTS_TINY => 'Tiny Dots',
            self::DOTS_OFFSET => 'Offset Dots',

            // Shapes
            self::SERRATED => 'Serrated',
            self::FLOWER => 'Flower',
            self::WAVE => 'Wave',
            self::ZIGZAG => 'Zigzag',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
