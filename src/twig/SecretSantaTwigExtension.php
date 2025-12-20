<?php

namespace nibiru\secretsanta\twig;

use craft\helpers\Cp;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SecretSantaTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('santaStatusLabel', [$this, 'statusLabel'], ['is_safe' => ['html']]),
        ];
    }

    public function statusLabel(array $config): string
    {
        // Cp::statusLabelHtml returns the proper CP markup
        return Cp::statusLabelHtml($config) ?? '';
    }
}
