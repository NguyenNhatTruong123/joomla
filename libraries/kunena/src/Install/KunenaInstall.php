<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\Install;

\defined('_JEXEC') or die();

use Exception;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 *
 */
\define('KUNENA_INSTALLER_PATH', __DIR__);
/**
 *
 */
\define('KUNENA_INSTALLER_ADMINPATH', \dirname(KUNENA_INSTALLER_PATH));
/**
 *
 */
\define('KUNENA_INSTALLER_SITEPATH', JPATH_SITE . '/components/' . basename(KUNENA_INSTALLER_ADMINPATH));
/**
 *
 */
\define('KUNENA_INSTALLER_MEDIAPATH', JPATH_SITE . '/media/kunena');

/**
 * Install Model for Kunena
 *
 * @since   Kunena 6.0
 */
class KunenaInstall extends BaseDatabaseModel
{
	/**
	 * @var     array|null
	 * @since   Kunena 6.0
	 */
	public $steps = null;

	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	protected $_versionprefix = false;

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected $_installed = [];

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected $_versions = [];

	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	protected $_action = false;

	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $_errormsg = null;

	/**
	 * @var     array|null
	 * @since   Kunena 6.0
	 */
	protected $_versiontablearray = null;

	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $_versionarray = null;

	private $tables;

	/**
	 * @var DatabaseDriver|null
	 * @since version
	 */
	private $db;

	/**
	 * @var array
	 * @since version
	 */
	private $_sbVersions;

	/**
	 * @var array
	 * @since version
	 */
	private $_fbVersions;

	/**
	 * @var \null[][]
	 * @since version
	 */
	private $_kVersions;

	/**
	 * @throws  Exception
	 * @since   Kunena 6.0
	 */
	public function __construct()
	{


		$this->db = Factory::getDBO();
	}

	/**
	 * Handles the package's post-flight routine
	 *
	 * @param   string               $type    Which action is happening (install|uninstall|discover_install|update)
	 * @param   PackageAdapter|null  $parent  The object responsible for running this script. NULL if running outside
	 *                                        of the package's script.
	 *
	 * @return  bool
	 */
	public function postflight(string $type, ?PackageAdapter $parent = null): bool
	{
		$this->createMenu();

		return true;
	}

	/**
	 * Create a Joomla menu for the main
	 * navigation tab and publish it in the Kunena module position kunena_menu.
	 * In addition it checks if there is a link to Kunena in any of the menus
	 * and if not, adds a forum link in the mainmenu.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @throws KunenaInstallerException
	 * @since   Kunena 6.0
	 */
	public function createMenu(): void
	{
		//KunenaFactory::loadLanguage('com_kunena.install', 'admin');
	    $menu = ['name' => Text::_('COM_KUNENA_MENU_ITEM_FORUM'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_FORUM_ALIAS'), 'forum'),
		         'link' => 'index.php?option=com_kunena&view=home', 'access' => 1, 'params' => ['catids' => 0], ];

		$this->buildMenu($menu);
	}

	/**
	 * Build the Kunena menu
	 *
	 * @param   array  $menu  menu
	 *
	 * @return  boolean
	 *
	 * @throws KunenaInstallerException
	 * @throws Exception
	 * @since   Kunena 6.0
	 */
	public function buildMenu(array $menu)
	{
		//$config = KunenaFactory::getConfig();
        $menu = ['name' => Text::_('COM_KUNENA_MENU_ITEM_FORUM'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_FORUM_ALIAS'), 'forum'),
            'link' => 'index.php?option=com_kunena&view=home', 'access' => 1, 'params' => ['catids' => 0], ];

		$component_id = (int) ComponentHelper::getComponent('com_kunena')->id;

		$languages = LanguageHelper::getLanguages('default');
		$langCode = $languages[0]->lang_code;

		// First fix all broken menu items
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__menu'))
			->set($db->quoteName('component_id') . ' = ' . $component_id)
			->where("link LIKE '%option=com_kunena%'")
			->andWhere('type = "component"');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			throw new KunenaInstallerException($e->getMessage(), $e->getCode());
		}

		$table = Table::getInstance('MenuType');
		$data  = [
			'menutype'    => 'kunenamenu',
			'title'       => Text::_('COM_KUNENA_MENU_TITLE'),
			'description' => Text::_('COM_KUNENA_MENU_TITLE_DESC'),
		];

		if (!$table->bind($data) || !$table->check())
		{
			// Menu already exists, do nothing
			return true;
		}

		if (!$table->store())
		{
			throw new KunenaInstallerException($table->getError());
		}

		$table = Table::getInstance('menu');
		$table->load(['menutype' => 'kunenamenu', 'link' => $menu ['link']]);
		$paramdata = ['menu-anchor_title'     => '',
		              'menu-anchor_css'       => '',
		              'menu_image'            => '',
		              'menu_text'             => 1,
		              'page_title'            => '',
		              'show_page_heading'     => 0,
		              'page_heading'          => '',
		              'pageclass_sfx'         => '',
		              'menu-meta_description' => '',
		              'robots'                => '',
		              'secure'                => 0, ];

		$gparams = new Registry($paramdata);

		$params = clone $gparams;
		$params->loadArray($menu['params']);
		$data = [
			'menutype'     => 'kunenamenu',
			'title'        => $menu ['name'],
			'alias'        => $menu ['alias'],
			'link'         => $menu ['link'],
			'type'         => 'component',
			'published'    => 1,
			'parentid'     => 1,
			'component_id' => $component_id,
			'access'       => $menu ['access'],
			'params'       => (string) $params,
			'home'         => 0,
			'language'     => '*',
			'client_id'    => 0,
		];
		$table->setLocation(1, 'last-child');

		if (!$table->bind($data) || !$table->check() || !$table->store())
		{
			$table->alias = 'kunena';

			if (!$table->check() || !$table->store())
			{
				// Menu already exists, do nothing
				return true;
			}
		}

		$parent      = $table;
		$defaultmenu = 0;

		/*foreach ($languages as $langCode => $language)
		{*/
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('com_kunena.install', JPATH_ADMINISTRATOR . '/components/com_kunena', $langCode);

			$submenu = [
			    'index'     => ['name' => Text::_('COM_KUNENA_MENU_ITEM_INDEX'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_INDEX_ALIAS'), 'index'),
				                'link' => 'index.php?option=com_kunena&view=category&layout=list', 'access' => 1, 'default' => 'categories', 'params' => [], ],
			    'recent'    => ['name' => Text::_('COM_KUNENA_MENU_ITEM_RECENT'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_RECENT_ALIAS'), 'recent'),
				                'link' => 'index.php?option=com_kunena&view=topics&mode=replies', 'access' => 1, 'default' => 'recent', 'params' => ['topics_catselection' => '', 'topics_categories' => '', 'topics_time' => ''], ],
			    'unread'    => ['name' => Text::_('COM_KUNENA_MENU_ITEM_UNREAD'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_UNREAD_ALIAS'), 'unread'),
				                'link' => 'index.php?option=com_kunena&view=topics&layout=unread', 'access' => 2, 'params' => [], ],
			    'newtopic'  => ['name' => Text::_('COM_KUNENA_MENU_ITEM_NEWTOPIC'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_NEWTOPIC_ALIAS'), 'newtopic'),
				                'link' => 'index.php?option=com_kunena&view=topic&layout=create', 'access' => 2, 'params' => [], ],
			    'noreplies' => ['name' => Text::_('COM_KUNENA_MENU_ITEM_NOREPLIES'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_NOREPLIES_ALIAS'), 'noreplies'),
				                'link' => 'index.php?option=com_kunena&view=topics&mode=noreplies', 'access' => 2, 'params' => ['topics_catselection' => '', 'topics_categories' => '', 'topics_time' => ''], ],
			    'mylatest'  => ['name' => Text::_('COM_KUNENA_MENU_ITEM_MYLATEST'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_MYLATEST_ALIAS'), 'mylatest'),
				                'link' => 'index.php?option=com_kunena&view=topics&layout=user&mode=default', 'access' => 2, 'default' => 'my', 'params' => ['topics_catselection' => '2', 'topics_categories' => '0', 'topics_time' => ''], ],
			    'profile'   => ['name' => Text::_('COM_KUNENA_MENU_ITEM_PROFILE'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_PROFILE_ALIAS'), 'profile'),
				                'link' => 'index.php?option=com_kunena&view=user', 'access' => 2, 'params' => ['integration' => 1], ],
			    'help'      => ['name' => Text::_('COM_KUNENA_MENU_ITEM_HELP'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_HELP_ALIAS'), 'help'),
				                'link' => 'index.php?option=com_kunena&view=misc', 'access' => 3, 'params' => ['body' => Text::_('COM_KUNENA_MENU_HELP_BODY'), 'body_format' => 'bbcode'], ],
			    'search'    => ['name' => Text::_('COM_KUNENA_MENU_ITEM_SEARCH'), 'alias' => ApplicationHelper::stringURLSafe(Text::_('COM_KUNENA_MENU_SEARCH_ALIAS'), 'search'),
				                'link' => 'index.php?option=com_kunena&view=search', 'access' => 1, 'params' => [], ],
			];

			foreach ($submenu as $menuitem)
			{
				$params = clone $gparams;
				$params->loadArray($menuitem['params']);
				$table = Table::getInstance('menu');
				$table->load(['menutype' => 'kunenamenu', 'link' => $menuitem ['link'], 'language' => $langCode]);
				$data = [
					'menutype'     => 'kunenamenu',
					'title'        => $menuitem ['name'],
					'alias'        => $menuitem ['alias'] . '-' . $langCode,
					'link'         => $menuitem ['link'],
					'type'         => 'component',
					'published'    => 1,
					'parentid'     => $parent->id,
					'component_id' => $component_id,
					'access'       => $menuitem ['access'],
					'params'       => (string) $params,
					'home'         => 0,
					'language'     => $langCode,
					'client_id'    => 0,
				];
				$table->setLocation($parent->id, 'last-child');

				if (!$table->bind($data) || !$table->check() || !$table->store())
				{
					throw new KunenaInstallerException($table->getError());
				}

				/*if (!$defaultmenu || (isset($menuitem ['default']) && $config->defaultPage == $menuitem ['default']))
				{
					$defaultmenu = $table->id;
				}*/
			}
		//}

		// Update forum menuitem to point into default page
		$parent->link .= "&defaultmenu={$defaultmenu}";

		if (!$parent->check() || !$parent->store())
		{
			throw new KunenaInstallerException($table->getError());
		}

		// Finally create alias
		$defaultmenu = AbstractMenu::getInstance('site')->getDefault();

		if (!$defaultmenu)
		{
			return true;
		}

		$table = Table::getInstance('menu');
		$table->load(['menutype' => $defaultmenu->menutype, 'type' => 'alias', 'title' => Text::_('COM_KUNENA_MENU_ITEM_FORUM'), 'language' => $langCode]);

		if (!$table->id)
		{
			$data = [
				'menutype' => $defaultmenu->menutype,
				'title' => Text::_('COM_KUNENA_MENU_ITEM_FORUM'),
				'alias' => 'kunena-' . Factory::getDate()->format('Y-m-d'),
				'note' => '',
				'link' => 'index.php?Itemid=' . $parent->id,
				'type' => 'alias',
				'published' => 0,
				'parent_id' => 1,
				'component_id' => 0,
				'checked_out' => null,
				'checked_out_time' => null,
				'browserNav' => 0,
				'access' => 1,
				'img' => '',
				'template_style_id' => 0,
				'params' => '{"aliasoptions":"' . (int) $parent->id . '","menu-anchor_title":"","menu-anchor_css":"","menu_image":""}',
				'home' => 0,
				'language' => '*',
				'client_id' => 0
			];

			$table->setLocation(1, 'last-child');
		}
		else
		{
			$data = [
				'alias'  => 'kunena-' . Factory::getDate()->format('Y-m-d'),
				'link'   => 'index.php?Itemid=' . $parent->id,
				'params' => '{"aliasoptions":"' . (int) $parent->id . '","menu-anchor_title":"","menu-anchor_css":"","menu_image":""}',
			];
		}

		if (!$table->bind($data))
		{
			throw new KunenaInstallerException($table->getError());
		}

		if (!$table->check() || !$table->store())
		{
			// Menu already exists, do nothing
			return true;
		}
	}
}
