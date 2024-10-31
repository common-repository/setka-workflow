<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class TranslationsRunner
 */
class TranslationsRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $translations Translations
         */
        $translations = self::getContainer()->get(Translations::class);
        $translations->loadTranslations();
    }
}
