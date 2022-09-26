<?php

namespace kaerol\useractivity\event;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class user_listener implements EventSubscriberInterface
{
	
	/* @var Container */
	protected $phpbb_container;
	
	/** @var \phpbb\auth\auth */
	protected $auth;
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;
	
	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;
	
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		Container $phpbb_container, 
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user)
	{		
		$this->auth = $auth;
		$this->config = $config;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->user->add_lang_ext('kaerol/useractivity', 'useractivity');
	}
	
	static public function getSubscribedEvents()
	{
		return array(
            'core.user_setup'		=> 'user_setup', // dane usera
		);
	}
	
	public function user_setup($event)
	{			
		$user_data = $event['user_data'];
		$is_kaerol = $user_data['user_id'] == 588;

		$this->template->assign_var('IS_KAEROL', $is_kaerol);	

		$user_activity_report_url = $this->helper->route('kaerol_useractivity_user_activity_report', array('hash' => generate_link_hash('user_activity_report')));
		
		$this->template->assign_var('U_KAEROL_USERACTIVITY_URL', $user_activity_report_url);
	}	
}
