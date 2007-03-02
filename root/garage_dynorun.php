<?php
/** 
*
* @package garage
* @version $Id$
* @copyright (c) 2005 phpBB Garage
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

define('IN_PHPBB', true);

//Let's Set The Root Dir For phpBB And Load Normal phpBB Required Files
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

//Start Session Management
$user->session_begin();
$auth->acl($user->data);

//Setup Lang Files
$user->setup(array('mods/garage'));

//Build All Garage Classes e.g $garage_images->
require($phpbb_root_path . 'includes/mods/class_garage_business.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_dynorun.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_image.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_insurance.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_modification.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_quartermile.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_template.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_vehicle.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_guestbook.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_model.' . $phpEx);

//Set The Page Title
$page_title = $user->lang['GARAGE'];

//Get All String Parameters And Make Safe
$params = array('mode' => 'mode', 'sort' => 'sort', 'start' => 'start', 'order' => 'order');
while(list($var, $param) = @each($params))
{
	$$var = request_var($param, '');
}

//Get All Non-String Parameters
$params = array('vid' => 'VID', 'vid' => 'VID', 'mid' => 'MID', 'did' => 'DID', 'qmid' => 'QMID', 'ins_id' => 'INS_ID', 'eid' => 'EID', 'image_id' => 'image_id', 'comment_id' => 'CMT_ID', 'bus_id' => 'BUS_ID');
while(list($var, $param) = @each($params))
{
	$$var = request_var($param, '');
}

//Build Inital Navlink...Yes Forum Name!! We Use phpBB3 Standard Navlink Process!!
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['GARAGE'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage.$phpEx"))
);

//Display MCP Link If Authorised
$template->assign_vars(array(
	'U_MCP'	=> ($auth->acl_get('m_garage')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=garage', true, $user->session_id) : '')
);

//Decide What Mode The User Is Doing
switch( $mode )
{
	case 'add_dynorun':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_dynorun.$phpEx?mode=add_dynorun&amp;VID=$vid");
		}

		//Let Check That Rollingroad Runs Are Allowed...If Not Redirect
		if (!$garage_config['enable_dynorun'] || !$auth->acl_get('u_garage_add_dynorun'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=18"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);
		
		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_dynorun.html')
		);

		$dynocentres 	= $garage_business->get_business_by_type(BUSINESS_DYNOCENTRE);

		//Get Vehicle Data For Navlinks
		$vehicle=$garage_vehicle->get_vehicle($vid);

		//Build Navlinks
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $vehicle['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"))
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['ADD_DYNORUN'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=add_dynorun&amp;VID=$vid"))
		);

		//Build Required HTML Components Like Drop Down Boxes.....
		$garage_template->attach_image('dynorun');
		$garage_template->nitrous_dropdown();
		$garage_template->power_dropdown('bhp_unit');
		$garage_template->power_dropdown('torque_unit');
		$garage_template->boost_dropdown();
		$garage_template->dynocentre_dropdown($dynocentres);
		$template->assign_vars(array(
			'L_TITLE'  			=> $user->lang['ADD_NEW_RUN'],
			'L_BUTTON'  			=> $user->lang['ADD_NEW_RUN'],
			'U_SUBMIT_BUSINESS_DYNOCENTRE'	=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=user_submit_business&amp;VID=$vid&amp;redirect=add_dynorun&amp;BUSINESS=" . BUSINESS_DYNOCENTRE ),
			'VID' 				=> $vid,
			'S_MODE_ACTION' 		=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=insert_dynorun"))
         	);

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;

	case 'insert_dynorun':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_dynorun.$phpEx?mode=add_dynorun&amp;VID=$vid");
		}

		//Let Check That Rollingroad Runs Are Allowed...If Not Redirect
		if (!$garage_config['enable_dynorun'])
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=18"));
		}

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_add_dynorun'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Get All Data Posted And Make It Safe To Use
		$params = array('dynocentre_id' => '', 'bhp' => '', 'bhp_unit' => '', 'torque' => '', 'torque_unit' => '', 'boost' => '', 'boost_unit' => '', 'nitrous' => '', 'peakpoint' => '');
		$data 	= $garage->process_vars($params);

		//Checks All Required Data Is Present
		$params = array('dynocentre_id', 'bhp', 'bhp_unit');
		$garage->check_required_vars($params);

		//Update The Dynorun With Data Acquired
		$did = $garage_dynorun->insert_dynorun($data);

		//Update The Time Now...In Case We Get Redirected During Image Processing
		$garage_vehicle->update_vehicle_time($vid);

		//If Any Image Variables Set Enter The Image Handling
		if ($garage_image->image_attached())
		{
			//Check For Remote & Local Image Quotas
			if ($garage_image->below_image_quotas())
			{
				//Create Thumbnail & DB Entry For Image
				$image_id = $garage_image->process_image_attached('dynorun', $did);
				//Insert Image Into Dynoruns Gallery
				$hilite = $garage_dynorun->hilite_exists($vid, $did);
				$garage_image->insert_dynorun_gallery_image($image_id, $hilite);
			}
			//You Have Reached Your Image Quota..Error Nicely
			else if ($garage_image->above_image_quotas())
			{
				redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=4"));
			}
		}
		//No Image Attached..We Need To Check If This Breaks The Site Rule
		else if (($garage_config['enable_dynorun_image_required'] == '1') AND ($data['bhp'] >= $garage_config['dynorun_image_required_limit']))
		{
			//That Time Requires An Image...Delete Entered Time And Notify User
			$garage_dynorun->delete_dynorun($did);
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=26"));
		}

		//If Needed Update Garage Config Telling Us We Have A Pending Item And Perform Notifications If Configured
		if ($garage_config['enable_dynorun_approval'])
		{
			$garage->pending_notification('unapproved_dynoruns');
		}

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	case 'edit_dynorun':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_dynorun.$phpEx?mode=edit_dynorun&amp;DID=$did&amp;VID=$vid");
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_dynorun.html')
		);

		//Build Navlinks
		$vehicle_data 	= $garage_vehicle->get_vehicle($vid);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $vehicle_data['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"))
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['EDIT_DYNORUN'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid&amp;DID=$did"))
		);

		//Pull Required Dynorun Data From DB
		$data = $garage_dynorun->get_dynorun($did);

		//See If We Got Sent Here By Pending Page...If So We Need To Tell Update To Redirect Correctly
		$params = array('PENDING' => '');
		$redirect = $garage->process_vars($params);

		$dynocentres 	= $garage_business->get_business_by_type(BUSINESS_DYNOCENTRE);

		//Build All Required HTML
		$garage_template->nitrous_dropdown($data['nitrous']);
		$garage_template->power_dropdown('bhp_unit', $data['bhp_unit']);
		$garage_template->power_dropdown('torque_unit', $data['torque_unit']);
		$garage_template->boost_dropdown($data['boost_unit']);
		$garage_template->dynocentre_dropdown($dynocentres, $data['dynocentre_id']);
		$template->assign_vars(array(
			'L_TITLE'  		=> $user->lang['EDIT_RUN'],
			'L_BUTTON'  		=> $user->lang['EDIT_RUN'],
			'U_EDIT_DATA' 		=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=edit_dynorun&amp;VID=$vid&amp;DID=$did"),
			'U_MANAGE_GALLERY' 	=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=edit_dynorun&amp;VID=$vid&amp;DID=$did#images"),
			'VID' 			=> $vid,
			'DID' 			=> $did,
			'BHP' 			=> $data['bhp'],
			'TORQUE' 		=> $data['torque'],
			'BOOST' 		=> $data['boost'],
			'NITROUS' 		=> $data['nitrous'],
			'PEAKPOINT' 		=> $data['peakpoint'],
			'PENDING_REDIRECT'	=> $redirect['PENDING'],
			'S_MODE_ACTION' 	=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=update_dynorun"),
			'S_IMAGE_MODE_ACTION' 	=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=insert_dynorun_image"),
		));

		//Let Check The User Is Allowed Perform This Action
		if ((!$auth->acl_get('u_garage_upload_image')) OR (!$auth->acl_get('u_garage_remote_image')))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=16"));
		}

		//Pre Build All Side Menus
		$garage_template->attach_image('dynorun');

		//Pull Dynorun Gallery Data From DB
		$data = $garage_image->get_dynorun_gallery($vid, $did);

		//Process Each Image From Dynorun Gallery
		for ($i = 0, $count = sizeof($data);$i < $count; $i++)
		{
			$template->assign_block_vars('pic_row', array(
				'U_IMAGE'	=> (($data[$i]['attach_id']) AND ($data[$i]['attach_is_image']) AND (!empty($data[$i]['attach_thumb_location'])) AND (!empty($data[$i]['attach_location']))) ? append_sid("{$phpbb_root_path}garage.$phpEx", "mode=view_image&amp;image_id=" . $data[$i]['attach_id']) : '',
				'U_REMOVE_IMAGE'=> append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=remove_dynorun_image&amp;&amp;VID=$vid&amp;DID=$did&amp;image_id=" . $data[$i]['attach_id']),
				'U_SET_HILITE'	=> ($data[$i]['hilite'] == 0) ? append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=set_dynorun_hilite&amp;image_id=" . $data[$i]['attach_id'] . "&amp;VID=$vid&amp;DID=$did") : '',
				'IMAGE' 	=> $phpbb_root_path . GARAGE_UPLOAD_PATH . $data[$i]['attach_thumb_location'],
				'IMAGE_TITLE' 	=> $data[$i]['attach_file'])
			);
		}

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;

	case 'update_dynorun':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_dynorun.$phpEx?mode=edit_dynorun&amp;DID=$did&amp;VID=$vid");
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Get All Data Posted And Make It Safe To Use
		$params = array('dynocentre_id' => '', 'bhp' => '', 'bhp_unit' => '', 'torque' => '', 'torque_unit' => '', 'boost' => '', 'boost_unit' => '', 'nitrous' => '', 'peakpoint' => '', 'editupload' => '', 'image_id' => '', 'pending_redirect' => '');
		$data 	= $garage->process_vars($params);

		//Checks All Required Data Is Present
		$params = array('dynocentre_id', 'bhp', 'bhp_unit');
		$garage->check_required_vars($params);

		//Update The Dynorun With Data Acquired
		$garage_dynorun->update_dynorun($data);

		//Update The Time Now...In Case We Get Redirected During Image Processing
		$garage_vehicle->update_vehicle_time($vid);

		//If Needed Update Garage Config Telling Us We Have A Pending Item And Perform Notifications If Configured
		if ($garage_config['enable_dynorun_approval'])
		{
			$garage->pending_notification('unapproved_dynoruns');
		}

		//If Editting From Pending Page Redirect Back To There Instead
		if ($data['pending_redirect'] == 'MCP')
		{
			redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=garage&amp;mode=unapproved_dynoruns"));
		}

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	case 'delete_dynorun':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_delete_dynorun'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Delete The Dynorun
		$garage_dynorun->delete_dynorun($did);

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	case 'insert_dynorun_image':

		//Let Check The User Is Allowed Perform This Action
		if ((!$auth->acl_get('u_garage_upload_image')) OR (!$auth->acl_get('u_garage_remote_image')))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=16"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//If Any Image Variables Set Enter The Image Handling
		if ($garage_image->image_attached())
		{
			//Check For Remote & Local Image Quotas
			if ($garage_image->below_image_quotas())
			{
				//Create Thumbnail & DB Entry For Image
				$image_id = $garage_image->process_image_attached('dynorun', $did);
				//Insert Image Into Dynorun Gallery
				$hilite = $garage_dynorun->hilite_exists($did);
				$garage_image->insert_dynorun_gallery_image($image_id, $hilite);
			}
			//You Have Reached Your Image Quota..Error Nicely
			else if ($garage_image->above_image_quotas())
			{
				redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=4"));
			}
		}

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=edit_dynorun&amp;VID=$vid&amp;DID=$did#images"));

		break;

	case 'set_dynorun_hilite':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Set All Images To Non Hilite So We Do Not End Up With Two Hilites & Then Set Hilite
		$garage->update_single_field(GARAGE_DYNORUN_GALLERY_TABLE, 'hilite', 0, 'dynorun_id', $did);
		$garage->update_single_field(GARAGE_DYNORUN_GALLERY_TABLE, 'hilite', 1, 'image_id', $image_id);

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=edit_dynorun&amp;VID=$vid&amp;DID=$did#images"));

		break;

	case 'remove_dynorun_image':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Remove Image From Dynorun Gallery & Deletes Image
		$garage_image->delete_dynorun_image($image_id);

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_dynorun.$phpEx", "mode=edit_dynorun&amp;VID=$vid&amp;DID=$did#images"));

		break;

	case 'view_dynorun':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_browse'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=15"));
		}

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_view_dynorun.html')
		);

		//Pull Required Modification Data From DB
		$data = $garage_dynorun->get_dynorun($did);

		//Build Navlinks
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $data['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_vehicle&amp;VID=$vid"))
		);

		//Get All Gallery Data Required
		$gallery_data = $garage_image->get_dynorun_gallery($vid, $did);
			
		//Process Each Image From Dynorun Gallery	
       		for ( $i = 0; $i < count($gallery_data); $i++ )
        	{
               		// Do we have a thumbnail?  If so, our job is simple here :)
			if ( (empty($gallery_data[$i]['attach_thumb_location']) == false) AND ($gallery_data[$i]['attach_thumb_location'] != $gallery_data[$i]['attach_location']) )
			{
				$template->assign_vars(array(
					'S_DISPLAY_GALLERIES' 	=> true,
				));

				$template->assign_block_vars('dynorun_image', array(
					'U_IMAGE' 	=> append_sid('garage.'.$phpEx.'?mode=view_image&amp;image_id='. $gallery_data[$i]['attach_id']),
					'IMAGE_NAME'	=> $gallery_data[$i]['attach_file'],
					'IMAGE_SOURCE'	=> $phpbb_root_path . GARAGE_UPLOAD_PATH . $gallery_data[$i]['attach_thumb_location'])
				);
               		} 
	       	}

		//Build The Owners Avatar Image If Any...
		$data['avatar'] = '';
		if ($data['user_avatar'] AND $user->optionget('viewavatars'))
		{
			$avatar_img = '';
			switch( $data['user_avatar_type'] )
			{
				case AVATAR_UPLOAD:
					$avatar_img = $config['avatar_path'] . '/' . $data['user_avatar'];
				break;

				case AVATAR_GALLERY:
					$avatar_img = $config['avatar_gallery_path'] . '/' . $data['user_avatar'];
				break;
			}
			$data['avatar'] = '<img src="' . $avatar_img . '" width="' . $data['user_avatar_width'] . '" height="' . $data['user_avatar_height'] . '" alt="" />';
		}

		$template->assign_vars(array(
			'U_VIEW_PROFILE' 	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=" . $data['user_id']),
			'YEAR' 			=> $data['made_year'],
			'MAKE' 			=> $data['make'],
			'MODEL' 		=> $data['model'],
			'USERNAME' 		=> $data['username'],
			'USERNAME_COLOUR'	=> get_username_string('colour', $data['user_id'], $data['username'], $data['user_colour']),
            		'AVATAR_IMG' 		=> $data['avatar'],
            		'DYNOCENTRE' 		=> $data['title'],
            		'BHP' 			=> $data['bhp'],
            		'BHP_UNIT'	 	=> $data['bhp_unit'],
            		'TORQUE' 		=> $data['torque'],
            		'TORQUE_UNIT' 		=> $data['torque_unit'],
            		'NITROUS' 		=> $data['nitrous'],
            		'BOOST' 		=> $data['boost'],
            		'BOOST_UNIT' 		=> $data['boost_unit'],
            		'PEAKPOINT' 		=> $data['peakpoint'],
         	));

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;
}

$garage_template->version_notice();

//Set Template Files In Used For Footer
$template->set_filenames(array(
	'garage_footer' => 'garage_footer.html')
);

//Generate Page Footer
page_footer();

?>
