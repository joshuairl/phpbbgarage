<?php
/** 
*
* @package garage
* @version $Id$
* @copyright (c) 2005 phpBB Garage
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);

/**
* Set root path & include standard phpBB files required
*/
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

/**
* Setup user session, authorisation & language 
*/
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/garage'));

/**
* Build All Garage Classes e.g $garage_images->
*/
require($phpbb_root_path . 'includes/mods/class_garage_service.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_business.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_template.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_vehicle.' . $phpEx);

/**
* Setup variables 
*/
$mode = request_var('mode', '');
$vid = request_var('VID', '');
$svid = request_var('SVID', '');
$eid = request_var('EID', '');

/**
* Build inital navlink..we use the standard phpBB3 breadcrumb process
*/
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['GARAGE'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage.$phpEx"))
);

/**
* Display the moderator control panel link if authorised
*/
$template->assign_vars(array(
	'U_MCP'	=> ($auth->acl_get('m_garage')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=garage', true, $user->session_id) : '')
);

/**
* Perform a set action based on value for $mode
*/
switch( $mode )
{
	/**
	* Display page to add new service history
	*/
	case 'add_service':
		/**
		* Check authorisation to perform action, redirecting to error screen if not
		*/
		if (!$auth->acl_get('u_garage_add_service') || $garage_config['enable_service'] == '0')
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		/**
		* Check vehicle ownership, only owners & moderators with correct permissions get past here
		*/
		$garage_vehicle->check_ownership($vid);

		/**
		* Get vehicle & garage business data from DB
		*/
		$vehicle = $garage_vehicle->get_vehicle($vid);
		$garages = $garage_business->get_business_by_type(BUSINESS_GARAGE);

		/**
		* Handle template declarations & assignments
		*/
		page_header($user->lang['GARAGE']);
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_service.html')
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $vehicle['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"))
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['ADD_SERVICE'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=add_service&amp;VID=$vid"))
		);
		$garage_template->garage_dropdown($garages);
		$garage_template->rating_dropdown('rating');
		$garage_template->service_type_dropdown();
		$template->assign_vars(array(
			'L_TITLE'  			=> $user->lang['ADD_SERVICE'],
			'L_BUTTON'  			=> $user->lang['ADD_SERVICE'],
			'VID' 				=> $vid,
			'S_MODE_ACTION' 		=> append_sid("{$phpbb_root_path}garage_service.$phpEx", "mode=insert_service"))
         	);
		$garage_template->sidemenu();
	break;

	/**
	* Insert new service history
	*/
	case 'insert_service':
		/**
		* Check authorisation to perform action, redirecting to error screen if not
		*/
		if (!$auth->acl_get('u_garage_add_service') || !$garage_config['enable_service'])
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		/**
		* Check vehicle ownership, only owners & moderators with correct permissions get past here
		*/
		$garage_vehicle->check_ownership($vid);

		/**
		* Get all required/optional data and check required data is present
		*/
		$params	= array('garage_id' => '', 'type_id' => '', 'price' => '', 'rating' => '', 'mileage' => '');
		$data 	= $garage->process_vars($params);
		$params = array('garage_id', 'type_id', 'mileage');
		$garage->check_required_vars($params);

		/**
		* Perform required DB work to create new service history
		*/
		$svid = $garage_service->insert_service($data);

		/**
		* Updates timestamp on vehicle, indicating it has been updated.
		* Updated vehicles are displayed on statistics page
		*/
		$garage_vehicle->update_vehicle_time($vid);

		/**
		* All work complete for mode, so redirect to correct page
		*/
		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));
	break;

	/**
	* Display page to edit an existing service history
	*/
	case 'edit_service':
		/**
		* Check user logged in, else redirecting to login with return address to get them back
		*/
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_service.$phpEx?mode=edit_service&amp;SVID=$svid&amp;VID=$vid");
		}

		/**
		* Check vehicle ownership, only owners & moderators with correct permissions get past here
		*/
		$garage_vehicle->check_ownership($vid);

		/**
		* Get vehicle, service history & garage business data from DB
		*/
		$vehicle	= $garage_vehicle->get_vehicle($vid);
		$data 		= $garage_service->get_service($svid);
		$garages 	= $garage_business->get_business_by_type(BUSINESS_GARAGE);

		/**
		* Handle template declarations & assignments
		*/
		page_header($user->lang['GARAGE']);
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_service.html')
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $vehicle['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"))
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['EDIT_SERVICE'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid&amp;SVID=$svid"))
		);
		$garage_template->garage_dropdown($garages, $data['garage_id']);
		$garage_template->rating_dropdown('rating', $data['rating']);
		$garage_template->service_type_dropdown($data['type_id']);
		$template->assign_vars(array(
			'L_TITLE'		=> $user->lang['EDIT_SERVICE'],
			'L_BUTTON'		=> $user->lang['EDIT_SERVICE'],
			'PRICE'			=> $data['price'],
			'MILEAGE'		=> $data['mileage'],
			'VID'			=> $vid,
			'SVID'			=> $svid,
			'S_MODE_ACTION' 	=> append_sid("{$phpbb_root_path}garage_service.$phpEx", "mode=update_service"))
		);
		$garage_template->sidemenu();
	break;

	/**
	* Update existing service history
	*/
	case 'update_service':
		/**
		* Check user logged in, else redirecting to login with return address to get them back
		*/
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_service.$phpEx?mode=edit_service&amp;SVID=$svid&amp;VID=$vid");
		}

		/**
		* Check vehicle ownership, only owners & moderators with correct permissions get past here
		*/
		$garage_vehicle->check_ownership($vid);

		/**
		* Get all required/optional data and check required data is present
		*/
		$params	= array('garage_id' => '', 'type_id' => '', 'price' => '', 'rating' => '', 'mileage' => '');
		$data = $garage->process_vars($params);
		$params = array('garage_id', 'type_id', 'mileage');
		$garage->check_required_vars($params);

		/**
		* Perform required DB work to update service history
		*/
		$garage_service->update_service($data);

		/**
		* Updates timestamp on vehicle, indicating it has been updated.
		* Updated vehicles are displayed on statistics page
		*/
		$garage_vehicle->update_vehicle_time($vid);

		/**
		* All work complete for mode, so redirect to correct page
		*/
		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));
	break;

	/**
	* Delete existing service history
	*/
	case 'delete_service':
		/**
		* Check authorisation to perform action, redirecting to error screen if not
		*/
		if (!$auth->acl_get('u_garage_delete_service'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		/**
		* Check vehicle ownership, only owners & moderators with correct permissions get past here
		*/
		$garage_vehicle->check_ownership($vid);

		/**
		* Perform required DB work to delete service history
		*/
		$garage_service->delete_service($svid);

		/**
		* Updates timestamp on vehicle, indicating it has been updated.
		* Updated vehicles are displayed on statistics page
		*/
		$garage_vehicle->update_vehicle_time($vid);

		/**
		* All work complete for mode, so redirect to correct page
		*/
		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));
	break;
}
$garage_template->version_notice();

$template->set_filenames(array(
	'garage_footer' => 'garage_footer.html')
);

page_footer();
?>