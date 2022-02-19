<?php
/**
 * @package    System plugin to redirect user to dedicated user group page following login.
 * @author     https://www.brainforge.co.uk
 * @copyright  (C) 2022 Jonathan Brain. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
 */

class PlgSystemBfredirectonlogin extends CMSPlugin
{
	protected   $app;
	public      $params;

	/**
	 */
	public function onAfterInitialise()
	{
		if ($this->app->isClient('site'))
		{
			$application = 0;
		}
		else if ($this->app->isClient('administrator'))
		{
			$application = 1;
		}
		else
		{
			// Ignore other applications, such as CLI.
			return;
		}

		$redirects = $this->params->get('redirects');
		if (empty($redirects))
		{
			return;
		}

		$identity = $this->app->getIdentity();

		$current = trim(
			substr(Uri::getInstance()->toString(['scheme', 'host', 'port', 'path', 'query']),
				strlen(Uri::base())), '/');

		foreach((array)$redirects as $redirect)
		{
			if ($redirect->state &&
				($redirect->application ?? 0) == $application &&
				in_array($redirect->usergroup, $identity->groups))
			{
				if (!empty($redirect->trigger))
				{
					if (!preg_match("\001" . $redirect->trigger . "\001", trim($current, '/')))
					{
						continue;
					}
				}

				if ($redirect->onceonly &&
					$this->app->getUserState('bfredirectonlogin.userid', 0) == $identity->id)
				{
					// Redirection previously used in this session
					return;
				}

				$this->app->setUserState('bfredirectonlogin.userid', $identity->id);

				$url = Route::_($redirect->target, false);
				$this->app->redirect($url);
				$this->app->close();
			}
		}
	}
}
