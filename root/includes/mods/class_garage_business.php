<?php
/** 
*
* @package garage
* @version $Id: memberlist.php,v 1.207 2007/01/26 16:05:14 acydburn Exp $
* @copyright (c) 2005 phpBB Garage
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

class garage_business
{
	var $classname = "garage_business";

	/*========================================================================*/
	// Inserts Business Into DB
	// Usage: insert_business(array());
	/*========================================================================*/
	function insert_business($data)
	{
		global $db, $garage_config;

		$pending = ($garage_config['enable_business_approval']) ? 1 : 0;

		$sql = 'INSERT INTO ' . GARAGE_BUSINESS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'title'		=> $data['title'],
			'address'	=> $data['address'],
			'telephone'	=> $data['telephone'],
			'fax'		=> $data['fax'],
			'website'	=> $data['website'],
			'email'		=> $data['email'],
			'opening_hours'	=> $data['opening_hours'],
			'insurance'	=> $data['insurance'],
			'garage'	=> $data['garage'],
			'retail'	=> $data['retail'],
			'product'	=> $data['product'],
			'dynocentre'	=> $data['dynocentre'],
			'pending'	=> $pending,
		));

		$result = $db->sql_query($sql);

		return;
	}

	/*========================================================================*/
	// Update Single Business
	// Usage: update_business(array());
	/*========================================================================*/
	function update_business($data)
	{
		global $db, $garage_config;

		$pending = ($data['pending'] == 0) ? 0 : $garage_config['enable_business_approval'];

		$update_sql = array(
			'title'		=> $data['title'],
			'address'	=> $data['address'],
			'telephone'	=> $data['telephone'],
			'fax'		=> $data['fax'],
			'website'	=> $data['website'],
			'email'		=> $data['email'],
			'opening_hours'	=> $data['opening_hours'],
			'insurance'	=> $data['insurance'],
			'garage'	=> $data['garage'],
			'retail'	=> $data['retail'],
			'product'	=> $data['product'],
			'dynocentre'	=> $data['dynocentre'],
			'pending'	=> $pending,
		);

		$sql = 'UPDATE ' . GARAGE_BUSINESS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $update_sql) . "
			WHERE id = " . $data['id'];

		$result = $db->sql_query($sql);

		return;
	}

	/*========================================================================*/
	// Select Single Business Data From DB
	// Usage: get_business('business id');
	/*========================================================================*/
	function get_business($bus_id)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'WHERE'		=>  "b.id = $bus_id"
		));

		$result = $db->sql_query($sql);
		$data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Business Data From DB
	// Usage: get_all_business();
	/*========================================================================*/
	function get_all_business()
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			)
		));

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Business Data From DB
	// Usage: get_all_business();
	/*========================================================================*/
	function get_reassign_business()
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'WHERE'		=> "b.pending = 0 AND b.id NOT IN ($exclude)",
			'ORDER_BY' 	=> 'b.title ASC'
		));

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Business Data From DB
	// Usage: get_all_business();
	/*========================================================================*/
	function get_business_by_type($type)
	{
		global $db;

		$data = null;
		$field = null;

		//Setup Field We Will Query Based On Constant
		if ($type == BUSINESS_INSURANCE)
		{
			$field = 'insurance';
		}
		else if ($type == BUSINESS_GARAGE)
		{
			$field = 'garage';
		}
		else if ($type == BUSINESS_RETAIL)
		{
			$field = 'retail';
		}
		else if ($type == BUSINESS_PRODUCT)
		{
			$field = 'product';
		}
		else if ($type == BUSINESS_DYNOCENTRE)
		{
			$field = 'dynocentre';
		}

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'WHERE'		=> "b.$field  = 1",
			'ORDER_BY'	=> "b.title ASC"

		));

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Pending Business Data From DB
	// Usage: get_pending_business();
	/*========================================================================*/
	function get_pending_business()
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'WHERE'		=>  "b.pending = 1"
		));

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Garage Business Data From DB
	// Usage: get_all_garage_business('additional where', 'row start point', 'limit');
	/*========================================================================*/
	function get_garage_business($where, $start = 0, $limit = 20)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*, SUM(install_rating) AS rating, COUNT(*) *10 AS total_rating',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(GARAGE_MODIFICATIONS_TABLE => 'm'),
					'ON'	=> 'b.id = m.installer_id'
				)		
			),
			'WHERE'		=>  "b.garage = 1 AND b.pending = 0 $where",
			'GROUP_BY'	=>  "b.id",
			'ODER_BY'	=>  "rating DESC"
		));

      		$result = $db->sql_query_limit($sql, $limit, $start);
		while( $row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Shop Business Data From DB
	// Usage: get_shop_business('additional where', 'row start point', 'limit');
	/*========================================================================*/
	function get_shop_business($where, $start = 0, $limit = 20)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*, SUM(purchase_rating) AS rating, COUNT(*) *10 AS total_rating',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE		=> 'b',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(GARAGE_MODIFICATIONS_TABLE => 'm'),
					'ON'	=> 'b.id = m.shop_id'
				)		
			),
			'WHERE'		=>  "b.retail = 1  AND b.pending =0 $where",
			'GROUP_BY'	=>  "b.id",
			'ODER_BY'	=>  "rating DESC"
		));

      		$result = $db->sql_query_limit($sql, $limit, $start);
		while( $row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Select All Insurance Business Data From DB
	// Usage: get_insurance_business('additional where', 'row start point', 'limit')
	/*========================================================================*/
	function get_insurance_business($where = null, $start = 0,  $limit = 20)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'b.*, COUNT(DISTINCT b.id) as total',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
			),
			'WHERE'		=>  "b.insurance = 1 AND b.pending = 0 $where",
			'GROUP_BY'	=>  "b.id"
		));

      		$result = $db->sql_query_limit($sql, $limit, $start);
		while( $row = $db->sql_fetchrow($result) )
		{
			$data[] = $row;
		}
      		$db->sql_freeresult($result);

		return $data;
	}

	/*========================================================================*/
	// Count Garage Business Data In DB
	// Usage: count_garage_business_data('additional where');
	/*========================================================================*/
	function count_insurance_business_data($additional_where)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'count(DISTINCT b.title) as total',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE		=> 'b',
			),
			'WHERE'		=>  "b.insurance = 1 AND b.pending =0 $additional_where"
		));

		$result = $db->sql_query($sql);
		$data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $data['total'];
	}


	/*========================================================================*/
	// Count Garage Business Data In DB
	// Usage: count_garage_business_data('additional where');
	/*========================================================================*/
	function count_garage_business_data($additional_where)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'count(DISTINCT b.title) as total',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE		=> 'b',
				GARAGE_MODIFICATIONS_TABLE	=> 'm',
			),
			'WHERE'		=>  "m.installer_id = b.id AND b.garage = 1 AND b.pending =0 $additional_where"
		));

		$result = $db->sql_query($sql);
		$data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $data['total'];
	}

	/*========================================================================*/
	// Count Shop Business Data In DB
	// Usage: count_shop_business_data('additional where');
	/*========================================================================*/
	function count_shop_business_data($additional_where)
	{
		global $db;

		$data = null;

		$sql = $db->sql_build_query('SELECT', 
			array(
			'SELECT'	=> 'COUNT(DISTINCT b.title) as total',
			'FROM'		=> array(
				GARAGE_BUSINESS_TABLE	=> 'b',
				GARAGE_MODIFICATIONS_TABLE	=> 'm',
			),
			'WHERE'		=>  "m.shop_id = b.id AND b.retail = 1 AND b.pending =0 $additional_where"
		));

		$result = $db->sql_query($sql);
		$data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $data['total'];
	}

	/*========================================================================*/
	// Count Shop Business Data In DB
	// Usage: count_shop_business_data('additional where');
	/*========================================================================*/
	function reassign_business($id_list)
	{
		global $template, $garage_template, $page_title, $phpbb_root_path, $phpEx;

		$exclude_list = null;
		for($i = 0; $i < count($id_list); $i++)
		{
			$data[] 	= $this->get_business($id_list[$i]);
			$exclude_list 	.= $id_list[$i] . ',';
		}
		$exclude_list = rtrim($exclude_list, ', ');

		//Generate Page Header
		page_header($page_title);

		//Set Template Files In Use For This Mode
		$template->set_filenames(array(
			'header' => 'garage_header.html',
			'body'   => 'garage_reassign_business.html')
		);

		//Build Dropdown Box Of Business's To Reassign It 
		$business = $this->get_reassign_business($exclude_list);
		$garage_template->reassign_business_dropdown($business);

		$business_names = null;
		for ($i = 0, $count = sizeof($data); $i < $count; $i++)
		{
			$business_names	.= $data[$i]['title'] . ',';
			$template->assign_block_vars('business', array(
				'ID'	=> $data[$i]['id'])
			);
		}
		$business_names = rtrim($business_names, ', ');

		//Set Up Template Varibles
		$template->assign_vars(array(
			'BUSINESS_NAMES'	=> $business_names,
			'S_MODE_ACTION'		=> append_sid("{$phpbb_root_path}garage.$phpEx", "mode=reassign_business"))
		);

		//Display Page...In Order Header->Menu->Body->Footer (Foot Gets Parsed At The Bottom)
		$garage_template->sidemenu();

		$garage_template->version_notice();

		//Set Template Files In Used For Footer
		$template->set_filenames(array(
			'garage_footer' => 'garage_footer.html')
		);

		//Generate Page Footer
		page_footer();
	}

	/*========================================================================*/
	// Delete Quartermile Entry Including Image 
	// Usage: delete_quartermile('quartermile id');
	/*========================================================================*/
	function delete_business($id)
	{
		global $garage, $garage_image;
	
		//Time To Delete The Actual Quartermile Time Now
		$garage->delete_rows(GARAGE_BUSINESS_TABLE, 'id', $id);

		return ;
	}

	/*========================================================================*/
	// Approve Dynoruns
	// Usage: approve_dynorun(array(), 'mode');
	/*========================================================================*/
	function approve_business($id_list, $mode)
	{
		global $phpbb_root_path, $phpEx, $garage;

		for($i = 0; $i < count($id_list); $i++)
		{
			$garage->update_single_field(GARAGE_BUSINESS_TABLE, 'pending', 0, 'id', $id_list[$i]);
		}

		redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=garage&amp;mode=unapproved_business"));
	}

	/*========================================================================*/
	// Approve Dynoruns
	// Usage: approve_quartermile(array(), 'mode');
	/*========================================================================*/
	function disapprove_business($id_list, $mode)
	{
		global $phpbb_root_path, $phpEx;

		for($i = 0; $i < count($id_list); $i++)
		{
			$this->delete_business($id_list[$i]);
		}

		redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=garage&amp;mode=unapproved_business"));
	}
}

$garage_business = new garage_business();

?>
