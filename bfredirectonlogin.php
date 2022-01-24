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
		// Only implemented for site.
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$redirects = $this->params->get('redirects');
		if (empty($redirects))
		{
			return;
		}

		$identity = $this->app->getIdentity();

		if ($this->app->getUserState('bfredirectonlogin.userid', 0) == $identity->id)
		{
			// Redirection previously used in this session
			return;
		}

		foreach((array)$redirects as $redirect)
		{
			if ($redirect->state && in_array($redirect->usergroup, $identity->groups))
			{
				$this->app->setUserState('bfredirectonlogin.userid', $identity->id);

				$url = Route::_($redirect->target, false);
				$this->app->redirect($url);
				$this->app->close();
			}
		}
	}
}
