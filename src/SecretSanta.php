<?php
namespace nibiru\secretsanta;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterSiteUrlRulesEvent;
use craft\log\MonologTarget;
use craft\services\Elements;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;

use yii\base\Event;
use yii\log\Dispatcher;

use nibiru\secretsanta\elements\SantaGroupElement;

use nibiru\secretsanta\services\DrawService;
use nibiru\secretsanta\services\EmailService;
use nibiru\secretsanta\services\GroupService;
use nibiru\secretsanta\services\MemberService;

use nibiru\secretsanta\models\SettingsModel;
use nibiru\secretsanta\traits\PluginTrait;
use nibiru\secretsanta\variables\SecretSantaVariable;

class SecretSanta extends Plugin
{

    use PluginTrait;

    public string $schemaVersion = '1.0.0';

    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'draw' => DrawService::class,
                'email' => EmailService::class,
                'group' => GroupService::class,
                'member' => MemberService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->registerVariables();
        $this->registerElementTypes();
        $this->registerEventHandlers();
        $this->registerCpRoutes();
        $this->registerLogTarget();

        // // Register services
        // $this->setComponents([
        //     'groups' => GroupService::class,
        //     'members' => MemberService::class,
        //     'draw' => DrawService::class,
        //     'emails' => EmailService::class,
        // ]);

    }

    public function getPluginName(): string
    {
        return Craft::t('secret-santa', $this->getSettings()->pluginName);
    }

    private function registerVariables(): void
    {
        // Twig variable: craft.secretSanta
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('secretSanta', SecretSantaVariable::class);
            }
        );
    }

    /**
     * @throws InvalidConfigException
     */
    private function registerEventHandlers(): void
    {

    }

    /**
     * Registers element types.
     */
    private function registerElementTypes(): void
    {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SantaGroupElement::class;
            }
        );

    }

    public function registerCpRoutes(): void
    {
        // CP routes
        Event::on(
            UrlManager::class, 
            UrlManager::EVENT_REGISTER_CP_URL_RULES, 
            function(RegisterUrlRulesEvent $event) {
                $event->rules['santa/<token:[A-Za-z0-9_-]+>'] = 'secret-santa/public/landing';
                $event->rules['secret-santa'] = 'secret-santa/groups/index';
                $event->rules['secret-santa/new'] = 'secret-santa/admin/new-group';
                $event->rules['secret-santa/group/<groupId:\d+>'] = 'secret-santa/admin/group';
                $event->rules['secret-santa/draw/<groupId:\d+>'] = 'secret-santa/admin/draw';
                $event->rules['secret-santa/group/<groupId:\d+>/member/<memberId:\d+>'] = 'secret-santa/member/index';
                $event->rules['secret-santa/groups'] = 'secret-santa/groups/index';
                $event->rules['secret-santa/groups/<groupId:\d+>'] = 'secret-santa/groups/edit';
                $event->rules['secret-santa/groups/new'] = 'secret-santa/groups/edit';
            }
        );
    }

    /**
     * @throws InvalidConfigException
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(SettingsModel::class);
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('secret-santa/settings/_index.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    public function getCpNavItem(): array
    {
        $item = parent::getCpNavItem();
        $item['label'] = Craft::t('secret-santa', 'Secret Santa');

        // 👇 this is the important line
        $item['url'] = 'secret-santa/groups';

        $item['subnav'] = [
            'groups' => [
                'label' => Craft::t('secret-santa', 'Groups'),
                'url' => 'secret-santa/groups',
            ],
        ];

        return $item;
    }

    /**
     * Registers a custom log target, keeping the format as simple as possible.
     *
     * @see LineFormatter::SIMPLE_FORMAT
     */
    private function registerLogTarget(): void
    {
        if (Craft::getLogger()->dispatcher instanceof Dispatcher) {
            Craft::getLogger()->dispatcher->targets['secret-santa'] = new MonologTarget([
                'name' => 'secret-santa',
                'categories' => ['secret-santa'],
                'level' => LogLevel::INFO,
                'logContext' => false,
                'allowLineBreaks' => false,
                'formatter' => new LineFormatter(
                    format: "[%datetime%] %message%\n",
                    dateFormat: 'Y-m-d H:i:s',
                ),
            ]);
        }
    }

}
