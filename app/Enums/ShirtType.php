<?php

namespace App\Enums;

enum ShirtType: string
{
    case SOLID = 'solid';
    case SLEEVES = 'sleeves';
    case GRADIENT = 'gradient';
    case RADIAL_GRADIENT = 'radial_gradient';
    case HEAT_GRADIENT = 'heat_gradient';
    case BURST_GRADIENT = 'burst_gradient';
    case HALF_VERTICAL = 'half_vertical';
    case HALF_HORIZONTAL = 'half_horizontal';
    case DIAGONAL = 'diagonal';
    case LINE_VERTICAL = 'line_vertical';
    case LINE_HORIZONTAL = 'line_horizontal';
    case SASH = 'sash';
    case STRIPES_VERTICAL = 'stripes_vertical';
    case STRIPES_HORIZONTAL = 'stripes_horizontal';
    case STRIPES_DIAGONAL = 'stripes_diagonal';
    case CARBON = 'carbon';
    case CHECKERED = 'checkered';
    case DIAMOND = 'diamond';
    case FLAG_GRID = 'flag_grid';
    case TRIANGLE_GRID = 'triangle_grid';
    case CROSS_FINE = 'cross_fine';
    case DOTS = 'dots';
    case SERRATED = 'serrated';
    case FLOWER = 'flower';
    case WAVE = 'wave';
    case CONFETTI = 'confetti';
    case FIREWORK = 'firework';
    case CRACK = 'crack';

    public function label(): string
    {
        return match ($this) {
            // Solid & Gradient
            self::SOLID => 'Solid',
            self::SLEEVES => 'Sleeves',
            self::GRADIENT => 'Gradient',
            self::RADIAL_GRADIENT => 'Radial Gradient',
            self::HEAT_GRADIENT => 'Heat Gradient',
            self::BURST_GRADIENT => 'Burst Gradient',

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
            self::CARBON => 'Carbon',

            // Dots & Grid
            self::CHECKERED => 'Checkered',
            self::DIAMOND => 'Diamond',
            self::FLAG_GRID => 'Grid Flags',
            self::TRIANGLE_GRID => 'Grid Triangles',
            self::CROSS_FINE => 'Fine Cross',
            self::DOTS => 'Dots',

            // Shapes
            self::SERRATED => 'Serrated',
            self::FLOWER => 'Flower',
            self::WAVE => 'Wave',
            self::CONFETTI => 'Confetti',
            self::FIREWORK => 'Firework',
            self::CRACK => 'Crack',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
