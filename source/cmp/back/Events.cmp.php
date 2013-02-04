<?php
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2013, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author SBND Techologies Ltd <info@sbnd.net>
* @package cms.cmp.back.events
* @version 1.0
*/

BASIC::init()->imported('Calendar.cmp','cmp/back');
/**
 * Events managment component
 * @author Vasya Alexieva
 *
 */
class Events extends CmsComponent{
    /**
     * Component db table
     * @access public
     * @var string
     */
	public $base = 'events';
	/**
	 * Filter template name, override the default 
	 * @access public
	 * @var string
	 */
	public $template_filter = 'cmp-filter_events.tpl';
	/**
	 * Reminder component object
	 * @access public
	 * @var string
	 */
	public $reminder_sys_name = 'events_reminder';
   /**
    * Main function - the constructor of the component
    * @access public
    * @return void
    */
	function main(){
		parent::main();
					
		$this->setField('title', array(
			'text' => BASIC_LANGUAGE::init()->get('events_title'),
			'lingual' => true,
			'perm' => '*',
		));
		
		 $this->setField('date_start',array(
            'text' => BASIC_LANGUAGE::init()->get('events_date_start'),
        	'formtype' => 'date',
        	'dbtype' => 'datetime',
   	    	'perm' => '*',
        	'attributes' => array(
        		'format' => '%Y-%m-%d %H:%M',
		 		'dataformat' => 'str'
        	),
        	'default' => (BASIC_URL::init()->request('fdate')) 
        ));   

        $this->setField('date_end',array(
            'text' => BASIC_LANGUAGE::init()->get('events_date_end'),
        	'formtype' => 'date',
        	'dbtype' => 'datetime',
   	    	'perm' => '*',
        	'attributes' => array(
        		'format' => '%Y-%m-%d %H:%M',
        		'dataformat' => 'str'
        	),
        	'default' => (BASIC_URL::init()->request('fdate')) 
        )); 
        
        $this->setField('location', array(
			'text' => BASIC_LANGUAGE::init()->get('events_location'),
			'lingual' => true,
		));
		
		$this->setField('desc', array( 
			'text' => BASIC_LANGUAGE::init()->get('events_description'),
			'formtype' => 'html',
			'dbtype' => 'longtext',
			'lingual' => true,
			'attributes' => array(
				'height' => 400,
				'width' => '100%'
			)
		));		
		
	}
	/**
	 * Map column for list view and return html for list view
	 * @access public
	 * @return string html
	 */
	function ActionList(){
		$this->sorting = new BasicSorting('date_start', false, $this->prefix);
		
	
		$this->obj_reminder = $this->buildChild($this->reminder_sys_name);
		
		$this->map('title'     ,	BASIC_LANGUAGE::init()->get('events_title')	 , 'formater', '', '');
		$this->map('date_start',	BASIC_LANGUAGE::init()->get('events_date_start'), 'formater', '', '');
		$this->map('date_end'  , 	BASIC_LANGUAGE::init()->get('events_date_end')	 , 'formater', '', '');
		$this->map('#reminder' ,    BASIC_LANGUAGE::init()->get('events_reminder')	 , 'formater', '', '');
		
		$this->setFilters();
 		
		
		return parent::ActionList();
	}
	/**
	 * Help method that format cells in list view
	 * 
	 * @access public
	 * @param string $val
	 * @param string $name
	 * @param array $row
	 * @return mix
	 */
	function formater($value, $index, $record_data = array()){
		if($index == 'date_start' || $index == 'date_end'){
			return date('Y-m-d H:i', strtotime($value));
		}
		
		if($index == '#reminder'){
			$this->obj_reminder->parent_id = $record_data['id'];
			$rdr_rem = $this->obj_reminder->getRecords();
			$rdr_rem->read();
			if($rdr_rem->num_rows()){
				$value = date('Y-m-d H:i', strtotime($rdr_rem->item('date')));
			}
			else{
				$value = '';
			}
		}
		
		return $value;
	}
	function ActionSave($id=1){
		return parent::ActionSave($id);
	}
	/**
	 * Create date filter for list view
	 * 
	 * @access public
	 * @return string html fro list view
	 * 
	 */
	function LIST_MANAGER($criteria = ''){
		if(!$criteria && !BASIC_URL::init()->request('fdate')){
			$criteria = " 
							AND 
								(
									(
										DATE_FORMAT(`date_start` ,'%Y-%m-%d') <= DATE_FORMAT(NOW() ,'%Y-%m-01') AND 
										DATE_FORMAT(`date_end` ,'%Y-%m-%d') >= DATE_FORMAT(NOW() ,'%Y-%m-01')
									) OR
									`date_start` LIKE CONCAT(DATE_FORMAT(NOW() ,'%Y-%m'),'%')  OR
									`date_end` LIKE CONCAT(DATE_FORMAT(NOW() ,'%Y-%m'),'%')
								)
								";
		}
		return parent::LIST_MANAGER($criteria);
	}
	/**
	 * Set filter fields, used in ActionList
	 * @access public
	 * @return void
	 */
	function setFilters(){
		
		/**
		 * 
		 * {v} = 'all' - show all events
		 * {v} = '2012-10' or '2012-9' - show events for month
		 * {v} = '2012-10-02' or '2012-1-1' - show events for date LENGTH('{v}') = 10
		 */
		$this->filter = new BasicFilter('f');
		$this->filter->template($this->template_filter);
		$this->filter->field('date', array( 
			'filter' => "
			AND  (
					('{v}' <> 'all' AND 
						(
							(
								LENGTH('{v}') > 7 AND 
								DATE_FORMAT(`date_start` ,'%Y-%m-%d') <= '{v}' AND 
								DATE_FORMAT(`date_end` ,'%Y-%m-%d') >= '{v}'
							) 
							OR (
								LENGTH('{v}') <= 7	AND 
								(
									(
										DATE_FORMAT(`date_start` ,'%Y-%m-%d') <= '{v}-01' AND 
										DATE_FORMAT(`date_end` ,'%Y-%m-%d') >= '{v}-01'
									) OR
									`date_start` LIKE '{v}%'  OR
									`date_end` LIKE ('{v}%')
								)
							)
						) 
					)
					OR 
					('{v}' = 'all')
				)",
			'attributes' => array(
				'id' => 'datefield'
			)
		));
		//fdate
		$month = NULL;
		$year  = NULL;
		$fdate = BASIC_URL::init()->request('fdate');
		if($fdate && $fdate !='all'){
			$date = explode('-', BASIC_URL::init()->request('fdate'));
			$month = isset($date[1])? $date[1] : NULL;
			$year  = isset($date[0])? $date[0] : NULL;
		}
		if(!$month || !$year){
			$month = NULL;
			$year  = NULL;
		}
		
		$calendar = Calendar::factory($month, $year);

		$calendar->standard('today')
			->standard('prev-next')
			->standard('holidays');
			
		$m = ($month)? $month : date('m',time());
		$y = ($year) ? $year  : date('Y',time());
		$rdr = $this->getRecords(null, " AND (
												(
													DATE_FORMAT(`date_start` ,'%Y-%m-%d') <= '".$y."-".$m."-01' AND 
													DATE_FORMAT(`date_end` ,'%Y-%m-%d') >= '".$y."-".$m."-01'
												) OR
												`date_start` LIKE '".$y."-".$m."%'  OR
												`date_end` LIKE '".$y."-".$m."%'
										)");
		$events = array();
		while($rdr->read()){
			if($rdr->item('date_start') < $y.'-'.$m.'-01'){
				$rdr->setItem('date_start', $y.'-'.$m.'-01');
			}
			if($rdr->item('date_end') > date("Y-m-t", strtotime($y.'-'.$m.'-01'))){
				$rdr->setItem('date_end', date("Y-m-t", strtotime($y.'-'.$m.'-01')));
			}
			$events[] = $rdr->getItems();
		}

		foreach($events as $v){
			
			$start_day =  date('Y-m-d',strtotime($v['date_start']));
			$end_day   =  date('Y-m-d',strtotime($v['date_end']));
			
			while ($start_day <= $end_day) {
				$tmp_date = strtotime($start_day);
				$event = $calendar->event()->condition('timestamp', $tmp_date)->title('title')->output('output');
				$calendar->attach($event);
				
				$start_day = date('Y-m-d',strtotime(date("Y-m-d", $tmp_date) . " +1 day"));
			}
			
		}
		
		$prev = $calendar->prev_month_url();
		$prev['m'] = ($prev['m'] < 10 ? '0' : '').$prev['m'];
		$next = $calendar->next_month_url();
		$next['m'] = ($next['m'] < 10 ? '0' : '').$next['m'];
		
		for($i = 0; $i <= 6; $i++){
			$week_days[] = BASIC_LANGUAGE::init()->get('events_weekday_'.$i);
		}
		
		BASIC_TEMPLATE2::init()->set(array(
			'prev_month'		  => $prev['y'].'-'.$prev['m'],
			'next_month' 		  => $next['y'].'-'.$next['m'],
			'current_mounth' 	  => $current_mounth = ($calendar->month() < 10 ? '0' : '').$calendar->month(),
			'current_mounth_text' => BASIC_LANGUAGE::init()->get('events_month_'.$current_mounth),
			'year' 				  => $calendar->year,
			'days' 				  => $calendar->days(3,$week_days),
			'weeks' 			  => $calendar->weeks()
		),$this->template_filter);
	
	}
	/**
	 * Define module settings fields, which values will override value of class properties
	 * @access public
	 * @return hashmap
	 */
	function settingsData(){
		return array(
			'base' 				 => $this->base,
			'reminder_sys_name'  => $this->reminder_sys_name,
			'template_filter' 	=> $this->template_filter
		);
	}
	/**
	 * Module settings fields description 
	 * @access public
	 * @return value
	 */
	function settingsUI(){
		return array(
			'base' => array(
				'text' => BASIC_LANGUAGE::init()->get('db_table')	
			),
			'reminder_sys_name' => array(
				'text' => BASIC_LANGUAGE::init()->get('events_reminder_sys_name')	
			),
			'template_filter' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_filter')	
			)
		);
	}
}