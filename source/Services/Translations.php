<?php
namespace Setka\Workflow\Services;

/**
 * Class Translations
 *
 * php /srv/www/wordpress-develop/tools/i18n/makepot.php wp-plugin \
/srv/www/wordpress-default/wp-content/plugins/setka-workflow/ \
/srv/www/wordpress-default/wp-content/plugins/setka-workflow/translations/setka-workflow.pot;
 */
class Translations
{
    /**
     * @var string Plugin text domain.
     */
    protected $textDomain;

    /**
     * @var string Path to plugin translations folder.
     */
    protected $translationsPath;

    /**
     * Translations constructor.
     *
     * @param string $textDomain
     * @param string $translationsPath
     */
    public function __construct($textDomain, $translationsPath)
    {
        $this->textDomain       = $textDomain;
        $this->translationsPath = $translationsPath;
    }

    /**
     * Load plugin translations.
     *
     * @return $this For chain calls.
     */
    public function loadTranslations()
    {
        load_plugin_textdomain(
            $this->textDomain,
            false,
            $this->translationsPath
        );

        return $this;
    }
}
