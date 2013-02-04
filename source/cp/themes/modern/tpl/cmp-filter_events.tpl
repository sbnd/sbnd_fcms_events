<script type="text/javascript">
 	function openDate(date){//debugger;
		$('#datefield').get(0).value = date;
		$('#calendarFilter').click();
		return false;
 	}
</script>
<div class="filtertab filteron" style="display:none;"><span><!-- lingual(filter) --></span></div>
<div class="filter toggle_containerfilter" style="display:none;">
	<input id="calendarFilter" type="submit" value="<!-- lingual(filter_btn) -->" class="button filterbutton" />
	<div class="cols">
	<!-- foreach(${fields},field) -->
		<div class="col">
			<label>${field.label}</label>
				${field.ctrl}
		</div>
	<!-- end -->
		<div class="clr"></div>
	</div>
	<div class="clr"></div>
</div>
<div class="calendar_cont">
	<div class="calendar">
		<table cellspacing="1" cellpadding="0">
		    <thead>
				<tr>
					<td colspan="7" class="calendar_nav">
						<span class="prev-month" ><a href="#" onclick="return openDate('${prev_month}')">&laquo;</a></span>
						<span  class="current-month">${current_mounth_text} ${year}</span>
						<span class="next-month" ><a href="#" onclick="return openDate('${next_month}')">&raquo;</a></span>
					</td>
				</tr>
		        <tr class="weekdays">
			        <!-- foreach(${days} as ${day}) -->
			       		<td> ${day} </td>
			        <!-- end -->
		        </tr>
		    </thead>
		    <tbody>
		        <!-- foreach(${weeks},n,week) -->
		            <tr>
		               <!-- foreach(${week},dn,day) -->
		                	<td class="day
		                		<!-- if(${dn} > 4) --> weekend<!-- end -->
		                		<!-- if(${day.2.classes.prev-next}) --> prev-next<!-- end -->
		                		<!-- if(${day.2.classes.holidays}) --> holydays<!-- end -->
		                		<!-- if(${day.2.classes.today}) --> today<!-- end -->
		                		<!-- if(${day.2.output}) --> event<!-- end -->
		                	" >
		                        <!-- if(${day.2.classes.prev-next}) -->
		                       		 ${day.0}
		                        <!-- else -->
		                        	<a onclick="return (${day.0} < 10) ? openDate('${year}-${current_mounth}-0${day.0}') : openDate('${year}-${current_mounth}-${day.0}')" href="#" class="date">${day.0}</a>
		                        <!-- end -->
		                    </td>
		               <!-- end -->
		            </tr>
		        <!-- end -->
		    </tbody>
		</table>
	</div>
	<div class="calendar_btns">
		<div>
			<input id="calendarFilter" onclick="return openDate('all')" type="submit" value="<!-- lingual(events_show_all_events) -->" class="button filterbutton" />
		</div>
		<div>
			<input id="calendarFilter" onclick="return openDate('${year}-${current_mounth}')" type="submit" value="<!-- lingual(events_show_selected_month_events) -->" class="button filterbutton" />
		</div>
		<div>
			<input id="calendarFilter" onclick="return openDate('')" type="submit" value="<!-- lingual(events_show_current_month_events) -->" class="button filterbutton" />
		</div>
	</div>
</div>