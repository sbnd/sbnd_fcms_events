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

/**
 * 
 * Events Reminder to child component of Events
 * @author Vasya Alexieva
 *
 */
class Events_Reminder extends CmsComponent{
    /**
     * Component db table
     * @access public
     * @var string
     */
	public $base = 'events_reminder';
   /**
    * Main function - the constructor of the component
    * @access public
    * @return void
    */
	function main(){
		parent::main();
				
		 $this->setField('date',array(
            'text' => BASIC_LANGUAGE::init()->get('events_date'),
        	'formtype' => 'date',
        	'dbtype' => 'date',
   	    	'perm' => '*',
        	'attributes' => array(
        		'format' => '%Y-%m-%d %H:%M',
		 		'dataformat' => 'str'
        	)
        ));  
        
         $this->setField('title', array(
			'text' => BASIC_LANGUAGE::init()->get('events_title'),
         	'perm' => '*',
			'lingual' => true,
		));
		
        $this->setField('message', array(
        	'text' => BASIC_LANGUAGE::init()->get('events_message'),
	        'formtype' => 'textarea',
        	'dbtype' => 'text',
   	    	'perm' => '*',
        	'lingual' => true,
        	'attributes' => array(
        		'maxlength' => 1000
        	)
        ));
        
	}
	/**
	 * Redirect to parent component
	 * @access public
	 * @return void
	 */
	function ActionBack(){
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(),'cmp=events');
	}
	/**
	 * Open form in list view. Support only one record for one parent /event record/
	 * 
	 * @access public
	 * @return string
	 */
	function ActionList(){
		$rdr = $this->getRecords();
		if(!$rdr->num_rows()){				
			$prn_rdr = $this->buildParent()->getRecords($this->parent_id);
			$prn_rdr->read();
			$this->updateField('date',array(
			 	'default' => $prn_rdr->item('date_start')
			));		
			return parent::ActionFormAdd();
		}
		else{
			$rdr->read();
			return parent::ActionFormEdit($rdr->item('id'));
		}
	}
	/**
	 * Save data in db
	 * @see CmsComponent::ActionSave()
	 */
	function ActionSave($id){
		$rdr = $this->getRecords();
		if($rdr->num_rows()){	
			$rdr->read();
			$id = $rdr->item('id');
		}
		parent::ActionSave($id);
	}
	/**
	 * Define module settings fields, which values will override value of class properties
	 * @access public
	 * @return hashmap
	 */
	function settingsData(){
		return array(
			'base' 				 => $this->base,
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
			)
		);
	}
}