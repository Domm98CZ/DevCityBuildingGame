<?php declare(strict_types=1);
namespace App\Service;

use Nette\Utils\Html;

final class IconService
{
    public const ICON_BASE = self::FA_ICON_STYLE_SOLID;

    public const FA_ICON_STYLE_BRANDS       = 'fab fa-brands';
    public const FA_ICON_STYLE_SOLID        = 'fas fa-solid';
    public const FA_ICON_STYLE_DUOTONE      = 'fad fa-duotone';
    public const FA_ICON_STYLE_REGULAR      = 'far fa-regular';
    public const FA_ICON_STYLE_THIN         = 'fat fa-thin';
    public const FA_ICON_STYLE_LIGHT        = 'fal fa-light';

    public const ICON_STYLE_LIST = [
        self::FA_ICON_STYLE_SOLID
        , self::FA_ICON_STYLE_DUOTONE
        , self::FA_ICON_STYLE_REGULAR
        , self::FA_ICON_STYLE_THIN
        , self::FA_ICON_STYLE_LIGHT
    ];

    public const
        ICON_HOME                       = 'home'
        , ICON_DEFAULT                  = 'default'
    ;

    private const ICONS_LIST = [
        self::ICON_HOME                 => 'fa-home'
        , self::ICON_DEFAULT            => 'fa-asterisk'
    ];

    private const ICON_FORCED_STYLE = [];

    private const ICON_ALIAS_LIST = [];

    private array $iconList = [];

    public function __construct()
    {
        $this->buildList();
    }

    /**
     * @param string $iconName
     * @return string
     */
    public function getIcon(string $iconName): string
    {
        return $this->iconList[$iconName] ?? $iconName;
    }

    /**
     * @param string $iconName
     * @return Html
     */
    public function getIconAsHtml(string $iconName): Html
    {
        return Html::el('i')->class($this->getIcon($iconName));
    }

    /**
     * @return void
     */
    private function buildList(): void
    {
        foreach (self::ICONS_LIST as $iconName => $icon) {
            $this->iconList[$iconName] = self::buildSingle($iconName, $icon);
        }

        foreach (self::ICON_ALIAS_LIST as $iconParent => $iconChildren) {
            foreach ($iconChildren as $iconChild) {
                if ($iconParent === $iconChild) {
                    continue;
                }
                $this->iconList[$iconChild] = $this->iconList[$iconParent];
            }
        }
    }

    /**
     * @param string|null $iconName
     * @param string $icon
     * @param string|null $forcedStyle
     * @return string
     */
    public static function buildSingle(?string $iconName, string $icon, ?string $forcedStyle = null): string
    {
        return sprintf('%s fa-fw %s', $forcedStyle ?? self::ICON_FORCED_STYLE[$iconName] ?? self::ICON_BASE, $icon);
    }

    /**
     * @param string $iconName
     * @param string|null $forcedStyle
     * @return string
     */
    public static function icon(string $iconName, ?string $forcedStyle = null): string
    {
        return sprintf('%s fa-fw %s', $forcedStyle ?? self::ICON_FORCED_STYLE[$iconName] ?? self::ICON_BASE, self::ICONS_LIST[$iconName] ?? self::ICON_HOME);
    }
}
