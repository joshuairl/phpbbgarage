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
require($phpbb_root_path . 'includes/mods/class_garage_track.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_service.' . $phpEx);
require($phpbb_root_path . 'includes/mods/class_garage_blog.' . $phpEx);

//Set The Page Title
$page_title = $user->lang['GARAGE'];

//Get All String Parameters And Make Safe
$params = array('mode' => 'mode', 'sort' => 'sort', 'start' => 'start', 'order' => 'order');
while(list($var, $param) = @each($params))
{
	$$var = request_var($param, '');
}

//Get All Non-String Parameters
$params = array( 'cid' => 'CID', 'vid' => 'VID', 'mid' => 'MID', 'did' => 'DID', 'qmid' => 'QMID', 'ins_id' => 'INS_ID', 'eid' => 'EID', 'image_id' => 'image_id', 'comment_id' => 'CMT_ID', 'bus_id' => 'BUS_ID');
while(list($var, $param) = @each($params))
{
	$$var = request_var($param, '');
}

$vid = (!empty($cid)) ? $cid : $vid; 

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
	//Mode To Display Create Vehicle Sceen
	case 'add_vehicle':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_vehicle.$phpEx?mode=add_vehicle");
		}

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_add_vehicle'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' 	=> 'garage_header.html',
			'body'   	=> 'garage_vehicle.html')
		);

		//Check To See If User Has Too Many Vehicles Already...If So Display Notice
		if ($garage_vehicle->count_user_vehicles() >= $garage_vehicle->get_user_add_quota())
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=5"));
		}

		//Set Make & Model If User Added Them...Else Use Default Values
		$params 	= array('MAKE_ID' => '', 'MODEL_ID' => '', 'YEAR' => '');
		$data 		= $garage->process_vars($params);
		$data['make_id'] = (empty($data['MAKE_ID'])) ? $garage_config['default_make_id'] : $data['MAKE_ID'];
		$data['model_id'] = (empty($data['MODEL_ID'])) ? $garage_config['default_model_id'] : $data['MODEL_ID'];

		//Get Required Data
		$years = $garage->year_list();
		$makes = $garage_model->get_all_makes();

		//Build All Required Javascript, Arrays & HTML
		$garage_template->attach_image('vehicle');
		$garage_template->make_dropdown($makes, $data['make_id']);
		$garage_template->engine_dropdown();
		$garage_template->currency_dropdown();
		$garage_template->mileage_dropdown();
		$garage_template->year_dropdown($years, $data['YEAR']);
		$template->assign_vars(array(
			'L_TITLE' 		=> $user->lang['CREATE_NEW_VEHICLE'],
			'L_BUTTON' 		=> $user->lang['CREATE_NEW_VEHICLE'],
			'U_USER_SUBMIT_MAKE' 	=> "javascript:add_make()",
			'U_USER_SUBMIT_MODEL' 	=> "javascript:add_model()",
			'MAKE_ID' 		=> $data['make_id'],
			'MODEL_ID'		=> $data['model_id'],
			'S_DISPLAY_SUBMIT_MAKE'	=> $garage_config['enable_user_submit_make'],
			'S_DISPLAY_SUBMIT_MODEL'=> $garage_config['enable_user_submit_make'],
			'S_MODE_ACTION_MAKE' 	=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=user_submit_make"),
			'S_MODE_ACTION_MODEL' 	=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=user_submit_model"),
			'S_MODE_ACTION' 	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=insert_vehicle"))
		);
		
		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();		

		break;

	//Mode To Actaully Insert Into DB A New Vehicle
	case 'insert_vehicle':

		//User Is Annoymous...So Not Allowed To Create A Vehicle
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_vehicle.$phpEx?mode=add_vehicle");
		}

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_add_vehicle'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Count Vehicles Already Owned
		$user_vehicle_count = $garage_vehicle->count_user_vehicles();

		//Check To See If User Has Too Many Vehicles Already...If So Display Notice
		if ($user_vehicle_count >= $garage_vehicle->get_user_add_quota()) 
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=5"));
		}

		//Get All Data Posted And Make It Safe To Use
		$params	= array('made_year' => '', 'make_id' => '', 'model_id' => '', 'colour' => '', 'mileage' => '', 'mileage_units' => '', 'price' => '', 'currency' => '', 'comments' => '', 'engine_type' => '');
		$data	= $garage->process_vars($params);

		//Set As Main User Vehicle If No Other Vehicle Exists For User
		$data['main_vehicle'] = ($user_vehicle_count == 0) ? 1 : 0;

		//Checks All Required Data Is Present
		$params = array('made_year', 'make_id', 'model_id');
		$garage->check_required_vars($params);

		//Insert The Vehicle Into The DB And Get The VID
		$vid = $garage_vehicle->insert_vehicle($data);

		//If Any Image Variables Set Enter The Image Handling
		if ($garage_image->image_attached())
		{
			//Check For Remote & Local Image Quotas
			if ($garage_image->below_image_quotas())
			{
				//Create Thumbnail & DB Entry For Image
				$image_id = $garage_image->process_image_attached('vehicle', $vid);
				//Insert Image Into Vehicles Gallery
				$hilite = $garage_vehicle->hilite_exists($vid);
				$garage_image->insert_vehicle_gallery_image($image_id, $hilite);
			}
			//You Have Reached Your Image Quota..Error Nicely
			else if ($garage_image->above_image_quotas())
			{
				redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=4"));
			}
		}

		//If Needed Perform Notifications If Configured
		if ($garage_config['enable_vehicle_approval'])
		{
			$garage->pending_notification('unapproved_vehicles');
		}

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	//Mode To Display Editting Page Of An Existing Vehicle
	case 'edit_vehicle':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_vehicle.$phpEx?mode=edit_vehicle&amp;VID=$vid");
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' 	=> 'garage_header.html',
			'body'   	=> 'garage_vehicle.html')
		);

		//Pull Required Vehicle Data From DB
		$data 	= $garage_vehicle->get_vehicle($vid);
		$years	= $garage->year_list();
		$makes 	= $garage_model->get_all_makes();

		//Build Navlinks
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $data['vehicle'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"))
		);
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['EDIT_VEHICLE'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid"))
		);

		//Build All Required Javascript And Arrays
		$garage_template->make_dropdown($makes, $data['make_id']);
		$garage_template->engine_dropdown($data['engine_type']);
		$garage_template->currency_dropdown($data['currency']);
		$garage_template->mileage_dropdown($data['mileage_unit']);
		$garage_template->year_dropdown($years, $data['made_year']);
		$template->assign_vars(array(
       			'L_TITLE' 		=> $user->lang['EDIT_VEHICLE'],
			'L_BUTTON' 		=> $user->lang['EDIT_VEHICLE'],
			'U_EDIT_DATA' 		=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid"),
			'U_MANAGE_GALLERY' 	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=manage_vehicle_gallery&amp;VID=$vid"),
			'U_USER_SUBMIT_MAKE' 	=> "javascript:add_make()",
			'U_USER_SUBMIT_MODEL' 	=> "javascript:add_model()",
			'VID' 			=> $vid,
			'MAKE' 			=> $data['make'],
			'MAKE_ID' 		=> $data['make_id'],
			'MODEL' 		=> $data['model'],
			'MODEL_ID' 		=> $data['model_id'],
			'COLOUR' 		=> $data['colour'],
			'MILEAGE' 		=> $data['mileage'],
			'PRICE' 		=> $data['price'],
			'COMMENTS' 		=> $data['comments'],
			'S_DISPLAY_SUBMIT_MAKE'	=> $garage_config['enable_user_submit_make'],
			'S_DISPLAY_SUBMIT_MODEL'=> $garage_config['enable_user_submit_make'],
			'S_MODE_ACTION_MAKE' 	=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=user_submit_make"),
			'S_MODE_ACTION_MODEL' 	=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=user_submit_model"),
			'S_MODE_ACTION'		=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=update_vehicle"),
			'S_IMAGE_MODE_ACTION' 	=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=insert_vehicle_image"),
		));

		//Let Check The User Is Allowed Perform This Action
		if ((!$auth->acl_get('u_garage_upload_image')) OR (!$auth->acl_get('u_garage_remote_image')))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=16"));
		}

		//Pre Build All Side Menus
		$garage_template->attach_image('vehicle');

		//Pull Vehicle Gallery Data From DB
		$data = $garage_image->get_vehicle_gallery($vid);

		//Process Each Image From Vehicle Gallery
		for ($i = 0, $count = sizeof($data);$i < $count; $i++)
		{
			$template->assign_block_vars('pic_row', array(
				'U_IMAGE'	=> (($data[$i]['attach_id']) AND ($data[$i]['attach_is_image']) AND (!empty($data[$i]['attach_thumb_location'])) AND (!empty($data[$i]['attach_location']))) ? append_sid("{$phpbb_root_path}garage.$phpEx", "mode=view_image&amp;image_id=" . $data[$i]['attach_id']) : '',
				'U_REMOVE_IMAGE'=> append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=remove_vehicle_image&amp;&amp;VID=$vid&amp;image_id=" . $data[$i]['attach_id']),
				'U_SET_HILITE'	=> ($data[$i]['hilite'] == 0) ? append_sid("{$phpbb_root_path}garage.$phpEx", "mode=set_vehicle_hilite&amp;image_id=" . $data[$i]['attach_id'] . "&amp;VID=$vid") : '',
				'IMAGE' 	=> $phpbb_root_path . GARAGE_UPLOAD_PATH . $data[$i]['attach_thumb_location'],
				'IMAGE_TITLE' 	=> $data[$i]['attach_file'])
			);
		}

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;

	//Mode To Actaully Update The DB Of An Existing Vehicle
	case 'update_vehicle':

		//Check The User Is Logged In...Else Send Them Off To Do So......And Redirect Them Back!!!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			login_box("garage_vehicle.$phpEx?mode=edit_vehicle&amp;VID=$vid");
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Get All Data Posted And Make It Safe To Use
		$params = array('made_year' => '', 'make_id' => '', 'model_id' => '', 'colour' => '', 'mileage' => '', 'mileage_units' => '', 'price' => '', 'currency' => '', 'comments' => '', 'engine_type' => '');
		$data = $garage->process_vars($params);

		//Checks All Required Data Is Present
		$params = array('made_year', 'make_id', 'model_id');
		$garage->check_required_vars($params);

		//Update The Vehicle With Data Acquired
		$garage_vehicle->update_vehicle($data);
	
		//Update Timestamp For Vehicle	
		$garage_vehicle->update_vehicle_time($vid);

		//If Needed perform Notifications If Configured
		if ($garage_config['enable_vehicle_approval'])
		{
			$garage->pending_notification('unapproved_vehicles');
		}

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	//Mode To Delete A Vehicle From The DB
	case 'delete_vehicle':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_delete_vehicle'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Actually Delete The Vehicle..This Will Delete All Related Items!!
		$garage_vehicle->delete_vehicle($vid);

		redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=main_menu"));

		break;

	case 'view_vehicle':

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
			'body'   => 'garage_view_vehicle_tabs.html')
		);

		//Display Vehicle With Owner Set to 'NO'
		$garage_vehicle->display_vehicle('NO');

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		//Update View Count For Vehicle
		$garage->update_view_count(GARAGE_VEHICLES_TABLE, 'views', 'id', $vid);

		break;

	case 'view_own_vehicle':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_view_vehicle_tabs.html')
		);

		//Display Vehicle With Owner Set to 'YES'
		$garage_vehicle->display_vehicle('YES');

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;

	case 'moderate_vehicle':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('m_garage'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=14"));
		}

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Build Page Header ;)
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_view_vehicle_tabs.html')
		);

		//Display Vehicle With Submode Set To 'MODERATE'
		$garage_vehicle->display_vehicle('MODERATE');

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		break;

	case 'set_main_vehicle':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Update All Vehicles They Own To Not Main Vehicle
		$garage->update_single_field(GARAGE_VEHICLES_TABLE, 'main_vehicle', 0 ,'user_id', $garage_vehicle->get_vehicle_owner_id($vid));

		//Now We Update This Vehicle To The Main Vehicle
		$garage->update_single_field(GARAGE_VEHICLES_TABLE, 'main_vehicle', 1, 'id', $vid);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_own_vehicle&amp;VID=$vid"));

		break;

	case 'insert_vehicle_image':

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
				$image_id = $garage_image->process_image_attached('vehicle', $vid);
				//Insert Image Into Vehicles Gallery
				$hilite = $garage_vehicle->hilite_exists($vid);
				$garage_image->insert_vehicle_gallery_image($image_id, $hilite);
			}
			//You Have Reached Your Image Quota..Error Nicely
			else if ($garage_image->above_image_quotas())
			{
				redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=4"));
			}
		}

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid#images"));

		break;

	case 'set_vehicle_hilite':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Set All Images To Non Hilite So We Do Not End Up With Two Hilites & Then Set Hilite
		$garage->update_single_field(GARAGE_VEHICLE_GALLERY_TABLE, 'hilite', 0, 'garage_id', $vid);
		$garage->update_single_field(GARAGE_VEHICLE_GALLERY_TABLE, 'hilite', 1, 'image_id', $image_id);

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid#images"));

		break;

	case 'remove_vehicle_image':

		//Check Vehicle Ownership
		$garage_vehicle->check_ownership($vid);

		//Remove Image From Vehicle Gallery & Deletes Image
		$garage_image->delete_vehicle_image($image_id);

		//Update Timestamp For Vehicle
		$garage_vehicle->update_vehicle_time($vid);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=edit_vehicle&amp;VID=$vid#images"));

		break;

	case 'rate_vehicle':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('u_garage_rate'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=17"));
		}

		//Get All Data Posted And Make It Safe To Use
		$params = array('vehicle_rating' => '');
		$data = $garage->process_vars($params);
		$data['rate_date']	= time();
		$data['user_id'] 	= $user->data['user_id'];

		//Checks All Required Data Is Present
		$params = array('vehicle_rating', 'rate_date', 'user_id');
		$garage->check_required_vars($params);

		//Pull Required Data From DB
	        $vehicle_data = $garage_vehicle->get_vehicle($vid);

		//If User Is Guest Generate Unique Number For User ID....
		srand($garage->make_seed());
		$data['user_id'] = ( $user->data['user_id'] == ANONYMOUS ) ? '-' . (rand(2,99999)) : $user->data['user_id'];

		//Check If User Owns Vehicle
		if ( $vehicle_data['user_id'] == $data['user_id'] )
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=21"));
		}

		$count = $garage_vehicle->count_user_vehicle_ratings($data['user_id']);

		//If You Have Not Rated This Vehicle..Create A Rating	
		if ( $count < 1 )
		{
			$garage_vehicle->insert_vehicle_rating($data);
		}
		//You Already Have Rated It..So Just Update The Rating	
		else
		{
			$garage_vehicle->update_vehicle_rating($data);
		}

		//Update The Weighted Rating Of This Vehicle
		$weighted_rating = $garage_vehicle->calculate_weighted_rating($vid);
		$garage_vehicle->update_weighted_rating($vid, $weighted_rating);

		redirect(append_sid("{$phpbb_root_path}garage_vehicle.$phpEx", "mode=view_vehicle&amp;VID=$vid"));

		break;

	case 'delete_vehicle_rating':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('m_garage'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=17"));
		}

		//Get All Data Posted And Make It Safe To Use
		$params = array('RTID' => '');
		$data = $garage->process_vars($params);

		//Checks All Required Data Is Present
		$params = array('RTID');
		$garage->check_required_vars($params);

		//Delete The Rating
		$garage->delete_rows(GARAGE_RATING_TABLE, 'id', $data['RTID']);

		//Update The Weighted Rating Of This Vehicle
		$weighted_rating = $garage_vehicle->calculate_weighted_rating($vid);
		$garage_vehicle->update_weighted_rating($vid, $weighted_rating);

		redirect(append_sid("garage_vehicle.$phpEx", "mode=moderate_vehicle&amp;VID=$vid", true));

		break;

	case 'reset_vehicle_rating':

		//Let Check The User Is Allowed Perform This Action
		if (!$auth->acl_get('m_garage'))
		{
			redirect(append_sid("{$phpbb_root_path}garage.$phpEx", "mode=error&amp;EID=17"));
		}

		//Let Get Vehicle Rating & Delete Them
		$data = $garage_vehicle->get_vehicle_rating($vid);
		for ($i = 0, $count = sizeof($data);$i < $count; $i++)
		{
			$garage->delete_rows(GARAGE_RATING_TABLE, 'id', $data['id']);
		}

		//Update The Weighted Rating Of This Vehicle
		$weighted_rating = $garage_vehicle->calculate_weighted_rating($vid);
		$garage_vehicle->update_weighted_rating($vid, $weighted_rating);

		redirect(append_sid("garage_vehicle.$phpEx", "mode=moderate_vehicle&amp;VID=$vid", true));

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
