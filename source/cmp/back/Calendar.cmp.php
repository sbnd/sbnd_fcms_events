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
* @package cms.cmp.back
* @version 1.0
*/

/**
 * Kohana event subject. Uses the SPL observer pattern.
 *
 * @author     Kohana Team, Corey Worrell
 * @copyright  (c) 2007-2008 Kohana Team
 * @version    1.0
 */
abstract class Event_Subject {

	// Attached subject listeners
	protected $listeners = array();

	/**
	 * Attach an observer to the object.
	 *
	 * @chainable
	 * @param   object  Event_Observer
	 * @return  object
	 */
	public function attach(Event_Observer $obj)
	{
		// Add a new listener
		$this->listeners[spl_object_hash($obj)] = $obj;

		return $this;
	}

	/**
	 * Detach an observer from the object.
	 *
	 * @chainable
	 * @param   object  Event_Observer
	 * @return  object
	 */
	public function detach(Event_Observer $obj)
	{
		// Remove the listener
		unset($this->listeners[spl_object_hash($obj)]);

		return $this;
	}

	/**
	 * Notify all attached observers of a new message.
	 *
	 * @chainable
	 * @param   mixed   message string, object, or array
	 * @return  object
	 */
	public function notify($message)
	{
		foreach ($this->listeners as $obj)
		{
			$obj->notify($message);
		}

		return $this;
	}

}

/**
 *  Kohana event observer. Uses the SPL observer pattern.
 *
 * @author     Kohana Team, Corey Worrell
 * @copyright  (c) 2007-2008 Kohana Team
 * @version    1.0
 */
abstract class Event_Observer {

	// Calling object
	protected $caller;

	/**
	 * Initializes a new observer and attaches the subject as the caller.
	 *
	 * @param   object  Event_Subject
	 * @return  void
	 */
	public function __construct(Event_Subject $caller)
	{
		// Update the caller
		$this->update($caller);
	}

	/**
	 * Updates the observer subject with a new caller.
	 *
	 * @chainable
	 * @param   object  Event_Subject
	 * @return  object
	 */
	public function update(Event_Subject $caller)
	{
		// Update the caller
		$this->caller = $caller;

		return $this;
	}

	/**
	 * Detaches this observer from the subject.
	 *
	 * @chainable
	 * @return  object
	 */
	public function remove()
	{
		// Detach this observer from the caller
		$this->caller->detach($this);

		return $this;
	}

	/**
	 * Notify the observer of a new message. This function must be defined in
	 * all observers and must take exactly one parameter of any type.
	 *
	 * @param   mixed   message string, object, or array
	 * @return  void
	 */
	abstract public function notify($message);

} // End Event Observer

/**
 *  Calendar event observer class.
 *
 * @author     Kohana Team, Corey Worrell
 * @copyright  (c) 2007-2008 Kohana Team
 * @version    1.0
 */
class Calendar_Event extends Event_Observer {

	// Boolean conditions
	protected $booleans = array
	(
		'current',
		'weekend',
		'first_day',
		'last_day',
		'last_occurrence',
		'easter',
	);

	// Rendering conditions
	protected $conditions = array();

	// Cell classes
	protected $classes = array();
	
	// Cell title
	protected $title = '';

	// Cell output
	protected $output = '';

	/**
	 * Adds a condition to the event. The condition can be one of the following:
	 *
	 * timestamp       - UNIX timestamp
	 * day             - day number (1-31)
	 * week            - week number (1-5)
	 * month           - month number (1-12)
	 * year            - year number (4 digits)
	 * day_of_week     - day of week (1-7)
	 * current         - active month (boolean) (only show data for the month being rendered)
	 * weekend         - weekend day (boolean)
	 * first_day       - first day of month (boolean)
	 * last_day        - last day of month (boolean)
	 * occurrence      - occurrence of the week day (1-5) (use with "day_of_week")
	 * last_occurrence - last occurrence of week day (boolean) (use with "day_of_week")
	 * easter          - Easter day (boolean)
	 * callback        - callback test (boolean)
	 *
	 * To unset a condition, call condition with a value of NULL.
	 *
	 * @chainable
	 * @param   string  condition key
	 * @param   mixed   condition value
	 * @return  object
	 */
	public function condition($key, $value)
	{
		if ($value === NULL)
		{
			unset($this->conditions[$key]);
		}
		else
		{
			if ($key === 'callback')
			{
				// Do nothing
			}
			elseif (in_array($key, $this->booleans))
			{
				// Make the value boolean
				$value = (bool) $value;
			}
			else
			{
				// Make the value an int
				$value = (int) $value;
			}

			$this->conditions[$key] = $value;
		}

		return $this;
	}

	/**
	 * Add a CSS class for this event. This can be called multiple times.
	 *
	 * @chainable
	 * @param   string  CSS class name
	 * @return  object
	 */
	public function add_class($class)
	{
		$this->classes[$class] = $class;

		return $this;
	}

	/**
	 * Remove a CSS class for this event. This can be called multiple times.
	 *
	 * @chainable
	 * @param   string  CSS class name
	 * @return  object
	 */
	public function remove_class($class)
	{
		unset($this->classes[$class]);

		return $this;
	}
	
	/**
	 * Add a title for this event.
	 *
	 * @chainable
	 * @param   string   Event title
	 * @return  object
	 */
	public function title($str)
	{
		$this->title = $str;
		
		return $this;
	}

	/**
	 * Set HTML output for this event.
	 *
	 * @chainable
	 * @param   string  HTML output
	 * @return  object
	 */
	public function output($str)
	{
		$this->output = $str;

		return $this;
	}

	/**
	 * Add a CSS class for this event. This can be called multiple times.
	 *
	 * @chainable
	 * @param   string  CSS class name
	 * @return  object
	 */
	public function notify($data)
	{
		// Split the date and current status
		list ($month, $day, $year, $week, $current) = $data;

		// Get a timestamp for the day
		$timestamp = mktime(0, 0, 0, $month, $day, $year);

		// Date conditionals
		$condition = array
		(
			'timestamp'   => (int) $timestamp,
			'day'         => (int) date('j', $timestamp),
			'week'        => (int) $week,
			'month'       => (int) date('n', $timestamp),
			'year'        => (int) date('Y', $timestamp),
			'day_of_week' => (int) date('w', $timestamp),
			'current'     => (bool) $current,
		);

		// Tested conditions
		$tested = array();

		foreach ($condition as $key => $value)
		{
			// Timestamps need to be handled carefully
			if($key === 'timestamp' AND isset($this->conditions['timestamp']))
			{
				// This adds 23 hours, 59 minutes and 59 seconds to today's timestamp, as 24 hours
				// is classed as a new day
				$next_day = $timestamp + 86399;
				
				if($this->conditions['timestamp'] < $timestamp OR $this->conditions['timestamp'] > $next_day)
					return FALSE;
			}
			// Test basic conditions first
			elseif (isset($this->conditions[$key]) AND $this->conditions[$key] !== $value)
				return FALSE;

			// Condition has been tested
			$tested[$key] = TRUE;
		}

		if (isset($this->conditions['weekend']))
		{
			// Weekday vs Weekend
			$condition['weekend'] = ($condition['day_of_week'] === 0 OR $condition['day_of_week'] === 6);
		}

		if (isset($this->conditions['first_day']))
		{
			// First day of month
			$condition['first_day'] = ($condition['day'] === 1);
		}

		if (isset($this->conditions['last_day']))
		{
			// Last day of month
			$condition['last_day'] = ($condition['day'] === (int) date('t', $timestamp));
		}

		if (isset($this->conditions['occurrence']))
		{
			// Get the occurance of the current day
			$condition['occurrence'] = $this->day_occurrence($timestamp);
		}

		if (isset($this->conditions['last_occurrence']))
		{
			// Test if the next occurance of this date is next month
			$condition['last_occurrence'] = ((int) date('n', $timestamp + 604800) !== $condition['month']);
		}

		if (isset($this->conditions['easter']))
		{
			if ($condition['month'] === 3 OR $condition['month'] === 4)
			{
				// This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
				// Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
				// 1876. This algorithm has also been published in the 1922 book General Astronomy by
				// Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
				// 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus.

				$a = $condition['year'] % 19;
				$b = (int) ($condition['year'] / 100);
				$c = $condition['year'] % 100;
				$d = (int) ($b / 4);
				$e = $b % 4;
				$f = (int) (($b + 8) / 25);
				$g = (int) (($b - $f + 1) / 3);
				$h = (19 * $a + $b - $d - $g + 15) % 30;
				$i = (int) ($c / 4);
				$k = $c % 4;
				$l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
				$m = (int) (($a + 11 * $h + 22 * $l) / 451);
				$p = ($h + $l - 7 * $m + 114) % 31;

				$month = (int) (($h + $l - 7 * $m + 114) / 31);
				$day = $p + 1;

				$condition['easter'] = ($condition['month'] === $month AND $condition['day'] === $day);
			}
			else
			{
				// Easter can only happen in March or April
				$condition['easter'] = FALSE;
			}
		}

		if (isset($this->conditions['callback']))
		{
			// Use a callback to determine validity
			$condition['callback'] = call_user_func($this->conditions['callback'], $condition, $this);
		}

		$conditions = array_diff_key($this->conditions, $tested);

		foreach ($conditions as $key => $value)
		{
			if ($key === 'callback')
			{
				// Callbacks are tested on a TRUE/FALSE basis
				$value = TRUE;
			}

			// Test advanced conditions
			if ($condition[$key] !== $value)
				return FALSE;
		}

		$this->caller->add_data(array
		(
			'classes' => $this->classes,
			'title'   => $this->title,
			'output'  => $this->output,
		));
	}

	/**
	 * Find the week day occurrence for a specific timestamp. The occurrence is
	 * relative to the current month. For example, the second Saturday of any
	 * given month will return "2" as the occurrence. This is used in combination
	 * with the "occurrence" condition.
	 *
	 * @param   integer  UNIX timestamp
	 * @return  integer
	 */
	protected function day_occurrence($timestamp)
	{
		// Get the current month for the timestamp
		$month = date('m', $timestamp);

		// Default occurrence is one
		$occurrence = 1;

		// Reduce the timestamp by one week for each loop. This has the added
		// benefit of preventing an infinite loop.
		while ($timestamp -= 604800)
		{
			if (date('m', $timestamp) !== $month)
			{
				// Once the timestamp has gone into the previous month, the
				// proper occurrence has been found.
				return $occurrence;
			}

			// Increment the occurrence
			$occurrence++;
		}
	}

}
/**
 * Calendar creation class.
 *
 * @author     Kohana Team, Corey Worrell
 * @copyright  (c) 2007-2008 Kohana Team
 * @version    1.0
 */
class Calendar extends Event_Subject {

	// Month and year to use for calendaring
	protected $month;
	protected $year;

	// Observed data
	protected $observed_data;
	
	// Configuration
	protected $config = array();

	/**
	 * Create a new Calendar instance. A month and year can be specified.
	 * By default, the current month and year are used.
	 *
	 * @param   integer  month number
	 * @param   integer  year number
	 * @return  object
	 */
	public static function factory($month = NULL, $year = NULL, $config = array())
	{
		return new Calendar($month, $year, $config);
	}

	/**
	 * Create a new Calendar instance. A month and year can be specified.
	 * By default, the current month and year are used.
	 *
	 * @param   integer  month number
	 * @param   integer  year number
	 * @return  void
	 */
	public function __construct($month = NULL, $year = NULL, $config = array())
	{
		empty($month) and $month = date('n'); // Current month
		empty($year)  and $year  = date('Y'); // Current year

		// Set the month and year
		$this->month = (int) $month;
		$this->year  = (int) $year;
		
		$this->config($config);
	}

	/**
	 * Allows fetching the current month and year.
	 *
	 * @param   string  key to get
	 * @return  mixed
	 */
	public function __get($key)
	{
		if ($key === 'month' OR $key === 'year')
		{
			return $this->$key;
		}
	}
	
	/**
	 * Returns an array of the names of the days, using the current locale.
	 *
	 * @param   integer  left of day names
	 * @return  array
	 */
	public function days($length = TRUE, $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'))
	{
		// strftime day format
		$format = ($length === TRUE OR $length > 3) ? '%A' : '%a';

		// Days of the week
		
		if ($this->config['week_start'] > 0)
		{
			for ($i = 0; $i < $this->config['week_start']; $i++)
			{
				array_push($days, array_shift($days));
			}
		}
		
		// Remove days that shouldn't be shown
		if (in_array(0, $this->config['show_days']))
		{
			for ($i = 0; $i < 7; $i++)
			{
				if ($this->config['show_days'][$i] === 0)
				{
					unset($days[$i]);
				}
			}
			
			$days = array_values($days);
		}

		// This is a bit awkward, but it works properly and is reliable
		foreach ($days as $i => $day)
		{
			// Convert the English names to i18n names
			$days[$i] = strftime($format, strtotime($day));
		}

		if (is_int($length) OR ctype_digit($length))
		{
			foreach ($days as $i => $day)
			{
				// Shorten the days to the expected length
				$days[$i] = substr($day, 0, $length);
			}
		}

		return $days;
	}
	
	/**
	 * Returns an array for use with a view. The array contains an array for
	 * each week. Each week contains 7 arrays, with a day number and status:
	 * TRUE if the day is in the month, FALSE if it is padding.
	 *
	 * @return  array
	 */
	public function weeks()
	{
		// First day of the month as a timestamp
		$first = mktime(1, 0, 0, $this->month, 1, $this->year);

		// Total number of days in this month
		$total = (int) date('t', $first);

		// Last day of the month as a timestamp
		$last  = mktime(1, 0, 0, $this->month, $total, $this->year);

		// Make the month and week empty arrays
		$month = $week = array();

		// Number of days added. When this reaches 7, start a new week
		$days = 0;
		$week_number = 1;

		if (($w = (int) date('w', $first) - $this->config['week_start']) < 0)
		{
			$w = (7 - $this->config['week_start']) + date('w', $first);
		}

		if ($w > 0)
		{
			// Number of days in the previous month
			$n = (int) date('t', mktime(1, 0, 0, $this->month - 1, 1, $this->year));

			// i = number of day, t = number of days to pad
			for ($i = $n - $w + 1, $t = $w; $t > 0; $t--, $i++)
			{
				// Notify the listeners
				$this->notify(array($this->month - 1, $i, $this->year, $week_number, FALSE));

				// Add previous month padding days
				$week[] = array($i, FALSE, $this->observed_data);
				$days++;
			}
		}

		// i = number of day
		for ($i = 1; $i <= $total; $i++)
		{
			if ($days % 7 === 0)
			{
				// Start a new week
				$month[] = $week;
				$week = array();

				$week_number++;
			}

			// Notify the listeners
			$this->notify(array($this->month, $i, $this->year, $week_number, TRUE));

			// Add days to this month
			$week[] = array($i, TRUE, $this->observed_data);
			$days++;
		}
		
		if (($w = (int) date('w', $last) - $this->config['week_start']) < 0)
		{
			$w = (7 - $this->config['week_start']) + date('w', $last);
		}

		if ($w >= 0)
		{
			// i = number of day, t = number of days to pad
			for ($i = 1, $t = 6 - $w; $t > 0; $t--, $i++)
			{
				// Notify the listeners
				$this->notify(array($this->month + 1, $i, $this->year, $week_number, FALSE));

				// Add next month padding days
				$week[] = array($i, FALSE, $this->observed_data);
			}
		}

		if ( ! empty($week))
		{
			// Append the remaining days
			$month[] = $week;
		}
		
		// Remove days that should't be shown.
		// TODO: Possibly figure out how to do this during the initial loops instead of after
		foreach ($month as $index => $week)
		{
			for ($i = 0; $i < 7; $i++)
			{
				if ($this->config['show_days'][$i] === 0)
				{
					unset($week[$i]);
				}
			}
			
			$remove_week = TRUE;
			foreach ($week as $day)
			{
				if ($day[1] === TRUE)
				{
					$remove_week = FALSE;
					break;
				}
			}
			
			if ($remove_week)
			{
				unset($month[$index]);
			}
			else
			{
				$month[$index] = array_values($week);
			}
		}

		return $month;
	}
	
	/**
	 * Calendar_Event factory method.
	 *
	 * @param   string  unique name for the event
	 * @return  object  Calendar_Event
	 */
	public function event()
	{
		return new Calendar_Event($this);
	}

	/**
	 * Calendar_Event factory method.
	 *
	 * @chainable
	 * @param   string  standard event type
	 * @return  object
	 */
	public function standard($name)
	{
		switch ($name)
		{
			case 'today':
				// Add an event for the current day
				$this->attach($this->event()->condition('timestamp', strtotime('today'))->add_class('today')->title('Today'));
			break;
			case 'prev-next':
				// Add an event for padding days
				$this->attach($this->event()->condition('current', FALSE)->add_class('prev-next'));
			break;
			case 'holidays':
				// Base event
				$event = $this->event()->condition('current', TRUE)->add_class('holiday');

				// Attach New Years
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 1)->condition('day', 1)->title('New Years')->output('New Years'));
				/*
				// Attach Valentine's Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 2)->condition('day', 14)->title('Valentine\'s Day')->output('Valentine\'s Day'));

				// Attach St. Patrick's Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 3)->condition('day', 17)->title('St. Patrick\'s Day')->output('St. Patrick\'s Day'));
				*/
				
				// Attach Easter
				$holiday = clone $event;
				$this->attach($holiday->condition('easter', TRUE)->title('Easter')->output('Easter'));
				/*
				// Attach Memorial Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 5)->condition('day_of_week', 1)->condition('last_occurrence', TRUE)->title('Memorial Day')->output('Memorial Day'));

				// Attach Independance Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 7)->condition('day', 4)->title('Independence Day')->output('Independence Day'));

				// Attach Labor Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 9)->condition('day_of_week', 1)->condition('occurrence', 1)->title('Labor Day')->output('Labor Day'));

				// Attach Halloween
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 10)->condition('day', 31)->title('Halloween')->output('Halloween'));

				// Attach Thanksgiving
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 11)->condition('day_of_week', 4)->condition('occurrence', 4)->title('Thanksgiving')->output('Thanksgiving'));
				*/
				// Attach Christmas
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 12)->condition('day', 25)->title('Christmas')->output('Christmas'));
			break;
			case 'weekends':
				// Weekend events
				$this->attach($this->event()->condition('weekend', TRUE)->add_class('weekend'));
			break;
		}

		return $this;
	}
	
	/**
	 * Get the URL for a previous month link
	 *
	 * @return  string
	 */
	public function prev_month_url()
	{
		$date  = mktime(0, 0, 0, $this->month - 1, 1, $this->year);
		$month = date('n', $date);
		$year  = date('Y', $date);
	//	$url   = self::query(array('m' => $month, 'y' => $year));
		$url   = array('m' => $month, 'y' => $year);
		return $url;
	}
	
	/**
	 * Get the previous month name
	 *
	 * @param   int/bool   Length of month name. Or 'TRUE' for full name, '0' or 'FALSE' for just '$before'
	 * @param   string     String to show before the month name
	 * @return  string     Month name
	 */
	public function prev_month($length = TRUE, $before = '&lsaquo; ')
	{
		$format = ($length === TRUE OR $length > 3) ? '%B' : '%b';
		
		$date = mktime(0, 0, 0, $this->month - 1, 1, $this->year);
		
		$month = strftime($format, $date);
		
		if (is_int($length) OR ctype_digit($length))
		{
			$month = substr($month, 0, $length);
		}
		
		if ($length === 0 OR $length === FALSE)
		{
			$month = '';
		}
		
		return $before.$month;
	}
	
	/**
	 * Get the current month name.
	 *
	 * @param   int/bool   Length of month name. Or 'TRUE' for full name.
	 * @return  string     Current month name
	 */
	public function month($length = TRUE)
	{
		return $this->month;
		/*
		$format = ($length === TRUE OR $length > 3) ? '%B' : '%b';
		
		$date = mktime(0, 0, 0, $this->month, 1, $this->year);
		
		$month = strftime($format, $date);
		
		if (is_int($length) OR ctype_digit($length))
		{
			$month = substr($month, 0, $length);
		}
		
		return $month;
		*/
	}
	
	/**
	 * Get the URL for a next month link
	 *
	 * @return  string
	 */
	public function next_month_url()
	{
		$date  = mktime(0, 0, 0, $this->month + 1, 1, $this->year);
		$month = date('n', $date);
		$year  = date('Y', $date);
	//	$url   = self::query(array('m' => $month, 'y' => $year));
		$url   = array('m' => $month, 'y' => $year);
		return $url;
	}
	
	/**
	 * Get the next month name
	 *
	 * @param   int/bool   Length of month name. Or 'TRUE' for full name, '0' or 'FALSE' for just '$after'
	 * @param   string     String to show after the month name
	 * @return  string     Month name
	 */
	public function next_month($length = TRUE, $after = ' &rsaquo;')
	{
		$format = ($length === TRUE OR $length > 3) ? '%B' : '%b';
		
		$date = mktime(0, 0, 0, $this->month + 1, 1, $this->year);
		
		$month = strftime($format, $date);
		
		if (is_int($length) OR ctype_digit($length))
		{
			$month = substr($month, 0, $length);
		}
		
		if ($length === 0 OR $length === FALSE)
		{
			$month = '';
		}
		
		return $month.$after;
	}


	/**
	 * Adds new data from an observer. All event data contains and array of CSS
	 * classes and an array of output messages.
	 *
	 * @param   array  observer data.
	 * @return  void
	 */
	public function add_data(array $data)
	{
		// Add new classes
		$this->observed_data['classes'] += $data['classes'];
		
		// Add titles
		$this->observed_data['title'][] = $data['title'];

		if ( ! empty($data['output']))
		{
			// Only add output if it's not empty
			$this->observed_data['output'][] = $data['output'];
		}
	}

	/**
	 * Resets the observed data and sends a notify to all attached events.
	 *
	 * @param   array  UNIX timestamp
	 * @return  void
	 */
	public function notify($data)
	{
		// Reset observed data
		$this->observed_data = array
		(
			'classes' => array(),
			'title'   => array(),
			'output'  => array(),
		);

		// Send a notify
		parent::notify($data);
	}
	
	/**
	 * Sets up the configuration for the Calendar, internal use only
	 *
	 * @param   array   Array with Calendar settings
	 * @return  void
	 */
	protected function config(array $config)
	{
		$defaults = array(
			'week_start' => 1,
			'show_days'  => array_fill(0, 7, 1),
		);
		
		$this->config = $config + $defaults;
	}
	
	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 *     // Returns "?sort=title&limit=10" combined with any existing GET values
	 *     $query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @param   array    array of GET parameters
	 * @param   boolean  include current request GET parameters
	 * @return  string
	 */
	protected static function query(array $params = NULL, $use_get = TRUE)
	{
		if ($use_get)
		{
			if ($params === NULL)
			{
				// Use only the current parameters
				$params = $_GET;
			}
			else
			{
				// Merge the current and new parameters
				$params = array_merge($_GET, $params);
			}
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		// Note: http_build_query returns an empty string for a params array with only NULL values
		$query = http_build_query($params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? '' : '?'.$query;
	}

}