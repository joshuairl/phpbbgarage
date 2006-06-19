<?php
/***************************************************************************
 *                              admin_garage_models.php
 *                            -------------------
 *   begin                : Friday, 06 May 2005
 *   copyright            : (C) Esmond Poynton
 *   email                : esmond.poynton@gmail.com
 *   description          : Provides Vehicle Garage System For phpBB
 *
 *   $Id: admin_garage_models.php 138 2006-06-07 15:55:46Z poyntesm $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_PHPBB', true);

if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['Garage']['Makes & Models'] = $filename;
	return;
}

//
// Let's set the root dir for phpBB
//
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('./pagestart.' . $phpEx);
require($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_garage.' . $phpEx);

//Build All Garage Classes e.g $garage_images->
require($phpbb_root_path . 'includes/class_garage.' . $phpEx);
require($phpbb_root_path . 'includes/class_garage_business.' . $phpEx);
require($phpbb_root_path . 'includes/class_garage_template.' . $phpEx);
require($phpbb_root_path . 'includes/class_garage_guestbook.' . $phpEx);
require($phpbb_root_path . 'includes/class_garage_model.' . $phpEx);

if( isset( $HTTP_POST_VARS['mode'] ) || isset( $HTTP_GET_VARS['mode'] ) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
	//message_die(GENERAL_ERROR, 'Mode Is .... ', '', __LINE__, __FILE__, $mode);
}
else
{
	$mode = '';
}

//Lets Setup Messages We Might Need...Just Easier On The Eye Doing This Seperatly
$missing_data_message = '<meta http-equiv="refresh" content="3;url=' . append_sid("admin_garage_models.$phpEx") . '">'. $lang['Missing_Required_Data']. "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$make_created_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['New_Make_Created'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$make_updated_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Make_Updated'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$make_updated_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Make_Updated'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$make_deleted_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Make_Deleted'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$make_exists_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Make_Exists'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$model_created_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['New_Model_Created'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$model_updated_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Model_Updated'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
$model_deleted_message = '<meta http-equiv="refresh" content="2;url=' . append_sid("admin_garage_models.$phpEx") . '">' . $lang['Model_Deleted'] . "<br /><br />" . sprintf($lang['Click_Return_Garage_Makes'], "<a href=\"" . append_sid("admin_garage_models.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

switch($mode)
{
	case 'insert_make':

		//Get All Data Posted And Make It Safe To Use
		$params = array('make');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('make');
		$garage->check_acp_required_vars($params, $missing_data_message);

		//Check For Make With Same Name And Error If Exists
		$count = $garage_model->count_make($data['make']);
		if ( $count > 0)
		{
			message_die(GENERAL_MESSAGE, $make_exists_message);
		}

		//Insert New Make Into DB
		$garage_model->insert_make($data);

		//Return a message...
		message_die(GENERAL_MESSAGE, $make_created_message);
				
		break;

	case 'update_make':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id', 'make');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id', 'make');
		$garage->check_acp_required_vars($params , $missing_data_message);

		//Check For Make With Same Name And Error If Exists
		$count = $garage_model->count_make($data['make']);
		if ( $count > 0)
		{
			message_die(GENERAL_MESSAGE, $make_exists_message);
		}

		//Update Make In DB
		$garage_model->update_make($data);

		//Return a message...
		message_die(GENERAL_MESSAGE, $make_updated_message);
		
		break;

	case 'make_set_pending':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id');
		$garage->check_acp_required_vars($params , $missing_data_message);

		//Set Make To Pending
		$garage->update_single_field(GARAGE_MAKES_TABLE, 'pending', '1' , 'id' , $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $make_updated_message);
		
		break;

	case 'make_set_approved':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id');
		$garage->check_acp_required_vars($params , $missing_data_message);

		//Set Make To Approved
		$garage->update_single_field(GARAGE_MAKES_TABLE, 'pending', '0' , 'id' , $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $make_updated_message);
		
		break;

	case 'confirm_delete_make':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);
		$data = $garage_model->select_make_data($data['id']);

		//Get All Make Data To Build Dropdown Of Where To Move Linked Items To
		$all_data = $garage_model->select_all_make_data('');

		//Build Dropdown Options For Where To Love Linked Items To
		for ($i = 0; $i < count($all_data); $i++)
		{
			//Do Not List Business We Are Deleting..
			if ( $data['id'] == $all_data[$i]['id'] )
			{
				continue;
			}
			$select_to .= '<option value="'. $all_data[$i]['id'] .'">'. $all_data[$i]['make'] .'</option>';
		}

		$template->set_filenames(array(
			'body' => 'admin/garage_confirm_delete.tpl')
		);

		$template->assign_vars(array(
			'S_GARAGE_ACTION' => append_sid("admin_garage_models.$phpEx?mode=delete_make&amp;id=".$data['id']),
			'S_TITLE' => $data['make'],
			'L_DELETE' => $lang['Delete_Make'],
			'L_DELETE_EXPLAIN' => $lang['Delete_Make_Explain'],
			'L_MOVE_CONTENTS' => $lang['Move_contents'],
			'L_MOVE_DELETE' => $lang['Move_and_Delete'],
			'L_REQUIRED' => $lang['Required'],
			'L_REMOVE' => $lang['Delete_Make'],
			'L_MOVE_DELETE' => $lang['Move_Delete_Make'],
			'L_MOVE_DELETE_BUTTON' => $lang['Delete_Make_Button'],
			'L_OR' => $lang['Or'],
			'L_DELETE_PERMENANTLY' => $lang['Delete_Permenantly'],
			'MOVE_TO_LIST' => $select_to)
		);

		$template->pparse('body');

		break;

	case 'delete_make':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id', 'target', 'permenant');
		$data = $garage->process_post_vars($params);

		//If Set Delete Permentantly..And Finish
		if ($data['permenant'] == '1')
		{
			//Delete The Model
			$garage->delete_rows(GARAGE_MAKES_TABLE, 'id', $data['id']);
	
			// Return a message...
			message_die(GENERAL_MESSAGE, $make_deleted_message);
		}

		//Checks All Required Data Is Present
		$params = array('id', 'target');
		$garage->check_acp_required_vars($params, $missing_data_message);

		//Move Any Existing Vehicles And Existing Models To New Target Make Then Delete Make
		$garage->update_single_field(GARAGE_TABLE,'make_id',$data['target'],'make_id',$data['id']);
		$garage->update_single_field(GARAGE_MODELS_TABLE,'make_id',$data['target'],'make_id',$data['id']);
		$garage->delete_rows(GARAGE_MAKES_TABLE, 'id', $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $make_deleted_message);

		break;	

	case 'insert_model':

		//Get All Data Posted And Make It Safe To Use
		$params = array('make_id', 'model');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('make_id', 'model');
		$garage->check_acp_required_vars($params, $missing_data_message);

		//Insert Make Into DB
		$garage_model->insert_model($data);

		//Return a message...
		message_die(GENERAL_MESSAGE, $model_created_message);
		
		break;	

	case 'update_model':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id', 'model');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id', 'model');
		$garage->check_acp_required_vars($params , $message);

		//Update Model In DB
		$garage_model->update_model($data);

		//Return a message...
		message_die(GENERAL_MESSAGE, $model_updated_message);
		
		break;

	case 'model_set_pending':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id');
		$garage->check_acp_required_vars($params , $message);

		//Set Model To Pending
		$garage->update_single_field(GARAGE_MODELS_TABLE, 'pending', '1' , 'id' , $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $model_updated_message);
		
		break;

	case 'model_set_approved':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);

		//Checks All Required Data Is Present
		$params = array('id');
		$garage->check_acp_required_vars($params , $message);

		//Set Model To Approved
		$garage->update_single_field(GARAGE_MODELS_TABLE, 'pending', '0' , 'id' , $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $model_updated_message);
		
		break;

	case 'confirm_delete_model':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id');
		$data = $garage->process_post_vars($params);
		$data = $garage_model->select_model_data($data['id']);

		//Get All Models For Make Data To Build Dropdown Of Where To Move Linked Items To
		$all_data = $garage_model->select_all_model_from_make_data($data['make_id']);

		//Build Dropdown Options For Where To Love Linked Items To
		for ($i = 0; $i < count($all_data); $i++)
		{
			//Do Not List Business We Are Deleting..
			if ( $data['id'] == $all_data[$i]['id'] )
			{
				continue;
			}
			$select_to .= '<option value="'. $all_data[$i]['id'] .'">'. $all_data[$i]['title'] .'</option>';
		}

		$template->set_filenames(array(
			'body' => 'admin/garage_confirm_delete.tpl')
		);

		$template->assign_vars(array(
			'S_GARAGE_ACTION' => append_sid("admin_garage_models.$phpEx?mode=delete_model&amp;id=".$data[0]['id']),
			'S_TITLE' => $data['model'],
			'L_DELETE' => $lang['Delete_Model'],
			'L_DELETE_EXPLAIN' => $lang['Delete_Model_Explain'],
			'L_MOVE_CONTENTS' => $lang['Move_contents'],
			'L_MOVE_DELETE' => $lang['Move_and_Delete'],
			'L_REQUIRED' => $lang['Required'],
			'L_REMOVE' => $lang['Delete_Model'],
			'L_MOVE_DELETE' => $lang['Move_Delete_Model'],
			'L_MOVE_DELETE_BUTTON' => $lang['Delete_Model_Button'],
			'L_OR' => $lang['Or'],
			'L_DELETE_PERMENANTLY' => $lang['Delete_Permenantly'],
			'MOVE_TO_LIST' => $select_to)
		);

		$template->pparse('body');

		break;

	case 'delete_model':

		//Get All Data Posted And Make It Safe To Use
		$params = array('id', 'target', 'permenant');
		$data = $garage->process_post_vars($params);

		//If Set Delete Permentantly..And Finish
		if ($data['permenant'] == '1')
		{
			//Delete The Model
			$garage->delete_rows(GARAGE_MODELS_TABLE, 'id', $data['id']);
	
			// Return a message...
			message_die(GENERAL_MESSAGE, $model_deleted_message);
		}

		//Checks All Required Data Is Present
		$params = array('id', 'target');
		$garage->check_acp_required_vars($params, $missing_data_message);

		//Move Any Existing Vehicles To New Target Model Then Delete Model
		$garage->update_single_field(GARAGE_TABLE,'model_id',$data['target'],'model_id',$data['id']);
		$garage->delete_rows(GARAGE_MODELS_TABLE, 'id', $data['id']);

		//Return a message...
		message_die(GENERAL_MESSAGE, $model_deleted_message);

		break;	

	default:

		$template->set_filenames(array(
			"body" => "admin/garage_makes_models.tpl")
		);

		//Get All Makes & Models
		$data = $garage_model->select_all_models_data('');

		//Build An Array Of Just Makes
		$makes = $garage->remove_duplicate($data, 'make_id');

		//Process Array For Each Make
		for( $i = 0; $i < count($makes); $i++ )
		{
			$status_mode =  ( $makes[$i]['make_pending'] == TRUE ) ? 'set_approved' : 'set_pending' ;
			$delete_url = append_sid("admin_garage_models.$phpEx?mode=confirm_delete_make&amp;id=" . $makes[$i]['make_id']);
			$status_url = append_sid("admin_garage_models.$phpEx?mode=make_$status_mode&amp;id=" . $makes[$i]['make_id']);
			$rename_url = 'javascript:rename('.$makes[$i]['make_id'].',1)';
			$add_model_url = 'javascript:add_model('.$makes[$i]['make_id'].')';

			//Set How The URL's Will Appear Since User Might Have Turned Images Off...
			$delete_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_delete'] . '" alt="'.$lang['Delete'].'" title="'.$lang['Delete'].'" border="0" />' : $lang['Delete'] ;
			$status_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_'.$status_mode] . '" alt="'.$lang[$status_mode].'" title="'.$lang[$status_mode].'" border="0" />' : $lang[$status_mode];
			$rename_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_edit'] . '" alt="'.$lang['Rename'].'" title="'.$lang['Rename'].'" border="0" />' : $lang['Rename'];
			$add_model_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_edit'] . '" alt="'.$lang['Add_Model_Button'].'" title="'.$lang['Add_Model_Button'].'" border="0" />' : $lang['Add_Model_Button'];

			$template->assign_block_vars('make', array(
				'COLOR' => ($i % 2) ? 'row1' : 'row2',
				'ID' => $makes[$i]['make_id'],
				'MAKE' => $makes[$i]['make'],
				'DELETE' => $delete_url_dsp,
				'STATUS' => $status_url_dsp,
				'RENAME' => $rename_url_dsp,
				'ADD_MODEL' => $add_model_url_dsp,
				'U_ADD_MODEL' => $add_model_url,
				'U_RENAME' => $rename_url,
				'U_DELETE' => $delete_url,
				'U_STATUS' => $status_url)
			);

			//Process Each Model Per Make
			for( $j = 0; $j < count($data); $j++ )
			{
				//Skip If Not Make Or Empty Model
				if ( ($makes[$i]['make_id'] != $data[$j]['make_id']) OR ( ($makes[$i]['make_id'] == $data[$j]['make_id']) AND (empty($data[$j]['model_id'])) ) )
				{
					continue;
				}

				$status_mode =  ( $data[$j]['model_pending'] == TRUE ) ? 'set_approved' : 'set_pending' ;
				$delete_url = append_sid("admin_garage_models.$phpEx?mode=confirm_delete_model&amp;id=" . $data[$j]['model_id']);
				$status_url = append_sid("admin_garage_models.$phpEx?mode=model_$status_mode&amp;id=" . $data[$j]['model_id']);
				$rename_url = 'javascript:rename('.$data[$j]['model_id'].',2)';

				//Set How The URL's Will Appear Since User Might Have Turned Images Off...
				$delete_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_delete'] . '" alt="'.$lang['Delete'].'" title="'.$lang['Delete'].'" border="0" />' : $lang['Delete'] ;
				$status_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_'.$status_mode] . '" alt="'.$lang[$status_mode].'" title="'.$lang[$status_mode].'" border="0" />' : $lang[$status_mode];
				$rename_url_dsp = ( $garage_config['garage_images'] ) ? '<img src="../' . $images['garage_edit'] . '" alt="'.$lang['Rename'].'" title="'.$lang['Rename'].'" border="0" />' : $lang['Rename'];

				$template->assign_block_vars('make.model', array(
					'COLOR' => ($j % 2) ? 'row1' : 'row2',
					'ID' => $data[$j]['model_id'],
					'MODEL' => $data[$j]['model'],
					'DELETE' => $delete_url_dsp,
					'STATUS' => $status_url_dsp,
					'RENAME' => $rename_url_dsp,
					'U_RENAME' => $rename_url,
					'U_DELETE' => $delete_url,
					'U_STATUS' => $status_url)
				);
			}
		}

		$template->assign_vars(array(
			'L_GARAGE_MODELS_TITLE' => $lang['Garage_Models_Title'],
			'L_GARAGE_MODELS_EXPLAIN' => $lang['Garage_Models_Explain'],
			'L_MAKE' => $lang['Make'],
			'L_MODEL' => $lang['Model'],
			'L_MODELS' => $lang['Models'],
			'L_ADD_MAKE' => $lang['Add_Make'],
			'L_ADD_MAKE_BUTTON' => $lang['Add_Make_Button'],
			'L_MODIFY_MAKE' => $lang['Modify_Make'],
			'L_MODIFY_MAKE_BUTTON' => $lang['Modify_Make_Button'],
			'L_DELETE_MAKE' => $lang['Delete_Make'],
			'L_DELETE_MAKE_BUTTON' => $lang['Delete_Make_Button'],
			'L_ADD_MODEL' => $lang['Add_Model'],
			'L_ADD_MODEL_BUTTON' => $lang['Add_Model_Button'],
			'L_MODIFY_MODEL' => $lang['Modify_Model'],
			'L_EMPTY_TITLE' => $lang['Empty_Title'],
			'L_CHOOSE_MODIFY_MODEL_BUTTON' => $lang['Choose_Modify_Model_Button'],
			'L_DELETE_MODEL' => $lang['Delete_Model'],
			'L_CHOOSE_DELETE_MODEL_BUTTON' => $lang['Choose_Delete_Model_Button'],
			'L_VEHICLE_MAKE' => $lang['Vehicle_Make'],
			'L_VEHICLE_MODEL' => $lang['Vehicle_Model'],
			'L_CHANGE_TO' => $lang['Change_To'],
			'L_EDIT' => $lang['Edit'],
			'L_STATUS' => $lang['Status'],
			'L_DELETE' => $lang['Delete'],
			'L_RENAME' => $lang['Rename'],
			'S_MODE_ACTION' => append_sid('admin_garage_models.'.$phpEx),
			'SHOW' => '<img src="../' . $images['garage_show_details'] . '" alt="'.$lang['Show_Details'].'" title="'.$lang['Show_Details'].'" border="0" />',
			'HIDE' => '<img src="../' . $images['garage_hide_details'] . '" alt="'.$lang['Hide_Details'].'" title="'.$lang['Hide_Details'].'" border="0" />')
		);

		$template->pparse("body");

}

include('./page_footer_admin.'.$phpEx);

?>